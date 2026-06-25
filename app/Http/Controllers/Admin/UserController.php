<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        $users = User::withCount('familyGroups')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    public function show(User $user)
    {
        $user->load(['familyGroups.owner', 'ownedFamilyGroups']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        // Evitar que un admin se quite a sí mismo el permiso y quede el sistema sin admins
        $becomingNonAdmin = $user->id === auth()->id() && empty($data['is_admin']);
        if ($becomingNonAdmin) {
            return back()->withErrors(['is_admin' => 'No podés quitarte el rol de admin a vos mismo.'])->withInput();
        }

        $user->update([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'is_admin' => (bool) ($data['is_admin'] ?? false),
        ]);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'No podés eliminarte a vos mismo.');

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Usuario \"{$name}\" eliminado.");
    }
}
