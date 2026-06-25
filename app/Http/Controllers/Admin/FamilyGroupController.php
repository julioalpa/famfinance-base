<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyGroupController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $groups = FamilyGroup::with('owner')
            ->withCount(['members', 'accounts', 'transactions'])
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.family-groups.index', compact('groups', 'search'));
    }

    public function show(FamilyGroup $familyGroup)
    {
        $familyGroup->load([
            'owner',
            'members',
            'invitations' => fn($q) => $q->where('status', 'pending'),
        ]);

        $availableUsers = User::whereNotIn('id', $familyGroup->members->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.family-groups.show', compact('familyGroup', 'availableUsers'));
    }

    public function edit(FamilyGroup $familyGroup)
    {
        $familyGroup->load('members');

        return view('admin.family-groups.edit', compact('familyGroup'));
    }

    public function update(Request $request, FamilyGroup $familyGroup)
    {
        $memberIds = $familyGroup->members()->pluck('users.id')->all();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'owner_id' => ['required', 'integer', 'in:' . implode(',', $memberIds ?: [0])],
        ]);

        DB::transaction(function () use ($familyGroup, $data) {
            $oldOwnerId = $familyGroup->owner_id;
            $newOwnerId = (int) $data['owner_id'];

            $familyGroup->update([
                'name'     => $data['name'],
                'owner_id' => $newOwnerId,
            ]);

            if ($oldOwnerId !== $newOwnerId) {
                $familyGroup->members()->updateExistingPivot($oldOwnerId, ['role' => 'member']);
                $familyGroup->members()->updateExistingPivot($newOwnerId, ['role' => 'owner']);
            }
        });

        return redirect()
            ->route('admin.family-groups.show', $familyGroup)
            ->with('success', 'Grupo actualizado.');
    }

    public function destroy(FamilyGroup $familyGroup)
    {
        $name = $familyGroup->name;
        $familyGroup->delete();

        return redirect()
            ->route('admin.family-groups.index')
            ->with('success', "Grupo \"{$name}\" eliminado.");
    }

    public function addMember(Request $request, FamilyGroup $familyGroup)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role'    => ['nullable', 'in:owner,member'],
        ]);

        if ($familyGroup->members()->where('users.id', $data['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Ese usuario ya es miembro del grupo.']);
        }

        $familyGroup->members()->attach($data['user_id'], [
            'role'      => $data['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Miembro agregado.');
    }

    public function removeMember(FamilyGroup $familyGroup, User $user)
    {
        abort_if($familyGroup->owner_id === $user->id, 422, 'No podés quitar al owner. Primero transferí la propiedad.');

        $familyGroup->members()->detach($user->id);

        return back()->with('success', "Se quitó a {$user->name} del grupo.");
    }
}
