<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToGroup
{
    /**
     * Verifica que el usuario autenticado pertenezca al grupo familiar activo
     * almacenado en la sesión. Si no tiene grupo, lo redirige a setup.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $groupId = session('active_family_group_id');

        if (! $groupId) {
            // Intentar asignar el primer grupo disponible automáticamente
            $firstGroup = $user->familyGroups()->first();

            if (! $firstGroup) {
                return redirect()->route('family-groups.setup')
                    ->with('info', 'Necesitás crear o unirte a un grupo familiar para continuar.');
            }

            session(['active_family_group_id' => $firstGroup->id]);
            $groupId = $firstGroup->id;
        }

        // Verificar que el usuario efectivamente pertenece al grupo
        $belongs = $user->familyGroups()->where('family_groups.id', $groupId)->exists();

        if (! $belongs) {
            session()->forget('active_family_group_id');
            return redirect()->route('family-groups.setup')
                ->withErrors(['group' => 'No tenés acceso a ese grupo familiar.']);
        }

        // Inyectar el grupo activo en la request para que los controllers lo usen
        $request->merge(['_active_family_group_id' => $groupId]);

        return $next($request);
    }
}
