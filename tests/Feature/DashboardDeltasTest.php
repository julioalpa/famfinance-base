<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardDeltasTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroupWithUser(): array
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('secret'),
        ]);

        $group = FamilyGroup::create([
            'name' => 'Familia Test',
            'owner_id' => $user->id,
        ]);

        $group->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        $account = Account::create([
            'family_group_id' => $group->id,
            'user_id' => $user->id,
            'name' => 'Efectivo',
            'type' => 'cash',
            'currency' => 'ARS',
            'initial_balance' => 0,
            'is_active' => true,
        ]);

        return [$user, $group, $account];
    }

    private function tx(int $groupId, int $userId, int $accountId, string $type, float $amount, string $date): void
    {
        Transaction::create([
            'family_group_id' => $groupId,
            'user_id' => $userId,
            'account_id' => $accountId,
            'type' => $type,
            'amount' => $amount,
            'currency' => 'ARS',
            'date' => $date,
        ]);
    }

    public function test_dashboard_shows_month_over_month_deltas(): void
    {
        [$user, $group, $account] = $this->makeGroupWithUser();

        $viewed = Carbon::create(2026, 5, 15);
        $prev = $viewed->copy()->subMonth();

        // Mes visualizado: ingresos 100.000, gastos 60.000
        $this->tx($group->id, $user->id, $account->id, 'income', 100000, $viewed->toDateString());
        $this->tx($group->id, $user->id, $account->id, 'expense', 60000, $viewed->toDateString());

        // Mes anterior: ingresos 80.000, gastos 50.000
        $this->tx($group->id, $user->id, $account->id, 'income', 80000, $prev->copy()->day(10)->toDateString());
        $this->tx($group->id, $user->id, $account->id, 'expense', 50000, $prev->copy()->day(10)->toDateString());

        $response = $this->actingAs($user)
            ->withSession(['active_family_group_id' => $group->id])
            ->get('/?month=2026-05');

        $response->assertStatus(200);

        // Ingresos: (100000-80000)/80000 = +25%
        $response->assertSee('25% vs mes ant.', false);
        // Gastos: (60000-50000)/50000 = +20%
        $response->assertSee('20% vs mes ant.', false);
        // Debe renderizar las pills de comparativa
        $response->assertSee('dash-delta', false);
    }

    public function test_dashboard_handles_no_previous_data(): void
    {
        [$user, $group, $account] = $this->makeGroupWithUser();

        $this->tx($group->id, $user->id, $account->id, 'income', 50000, '2026-05-15');

        $response = $this->actingAs($user)
            ->withSession(['active_family_group_id' => $group->id])
            ->get('/?month=2026-05');

        // Sin datos previos no debe romper (division por cero evitada)
        $response->assertStatus(200);
    }
}
