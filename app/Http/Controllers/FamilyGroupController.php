<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteMemberRequest;
use App\Http\Requests\StoreFamilyGroupRequest;
use App\Models\FamilyGroup;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class FamilyGroupController extends Controller
{
    /**
     * Página inicial cuando el usuario no tiene grupo.
     * Permite crear uno nuevo o pegar un link de invitación.
     */
    public function setup()
    {
        return view('family-groups.setup');
    }

    public function store(StoreFamilyGroupRequest $request)
    {
        $group = FamilyGroup::create([
            'name'     => $request->name,
            'owner_id' => auth()->id(),
        ]);

        // Agregar al creador como owner en la pivot
        $group->members()->attach(auth()->id(), [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);

        session(['active_family_group_id' => $group->id]);

        return redirect()
            ->route('dashboard')
            ->with('success', "Grupo \"{$group->name}\" creado. ¡Invitá a tu familia!");
    }

    public function show(FamilyGroup $familyGroup)
    {
        $this->authorizeGroup($familyGroup);

        $familyGroup->load(['members', 'invitations' => fn($q) => $q->where('status', 'pending')]);

        return view('family-groups.show', compact('familyGroup'));
    }

    public function switchGroup(Request $request, FamilyGroup $familyGroup)
    {
        $belongs = auth()->user()->familyGroups()
            ->where('family_groups.id', $familyGroup->id)
            ->exists();

        abort_if(! $belongs, 403);

        session(['active_family_group_id' => $familyGroup->id]);

        return redirect()->back()->with('success', "Cambiaste al grupo \"{$familyGroup->name}\".");
    }

    // ─── Invitaciones ────────────────────────────────────────────────────────

    public function invite(InviteMemberRequest $request, FamilyGroup $familyGroup)
    {
        $this->authorizeGroup($familyGroup);

        // Verificar que no esté ya en el grupo
        $alreadyMember = $familyGroup->members()
            ->where('users.email', $request->email)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Ese email ya es miembro del grupo.']);
        }

        // Invalidar invitaciones previas pendientes para el mismo email
        $familyGroup->invitations()
            ->where('email', $request->email)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        $invitation = Invitation::create([
            'family_group_id' => $familyGroup->id,
            'invited_by'      => auth()->id(),
            'email'           => $request->email,
        ]);

        // Enviar email con el link de invitación
        Mail::to($request->email)->send(new \App\Mail\FamilyGroupInvitation($invitation));

        return back()->with('success', "Invitación enviada a {$request->email}.");
    }

    public function acceptInvitation(string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);
            return redirect()->route('login')
                ->withErrors(['invite' => 'La invitación expiró. Pedí una nueva.']);
        }

        // Si el usuario no está logueado, guardar el token en sesión y mandarlo a login
        if (! auth()->check()) {
            session(['pending_invitation_token' => $token]);
            return redirect()->route('auth.google')
                ->with('info', 'Iniciá sesión con Google para aceptar la invitación.');
        }

        $user  = auth()->user();
        $group = $invitation->familyGroup;

        // Verificar que el email del usuario coincida con el de la invitación
        if ($user->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->withErrors(['invite' => 'Esta invitación es para otro email.']);
        }

        $group->members()->syncWithoutDetaching([
            $user->id => ['role' => 'member', 'joined_at' => now()],
        ]);

        $invitation->update(['status' => 'accepted']);
        session(['active_family_group_id' => $group->id]);

        return redirect()->route('dashboard')
            ->with('success', "Te uniste al grupo \"{$group->name}\".");
    }

    public function removeMember(FamilyGroup $familyGroup, int $userId)
    {
        $this->authorizeGroup($familyGroup);

        // Solo el owner puede remover miembros, y no puede removerse a sí mismo
        abort_if($familyGroup->owner_id !== auth()->id(), 403);
        abort_if($userId === auth()->id(), 422, 'No podés removerte a vos mismo.');

        $familyGroup->members()->detach($userId);

        return back()->with('success', 'Miembro removido del grupo.');
    }

    private function authorizeGroup(FamilyGroup $group): void
    {
        $belongs = auth()->user()->familyGroups()
            ->where('family_groups.id', $group->id)
            ->exists();

        abort_if(! $belongs, 403);
    }
}
