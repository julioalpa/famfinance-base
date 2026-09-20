<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDailyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => bcrypt('secret'),
        ]);

        $this->group = FamilyGroup::create([
            'name' => 'Familia Test',
            'owner_id' => $this->user->id,
        ]);
        $this->group->members()->attach($this->user->id, ['role' => 'owner', 'joined_at' => now()]);

        $this->account = Account::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'name' => 'Efectivo',
            'type' => 'cash',
            'currency' => 'ARS',
            'initial_balance' => 0,
            'is_active' => true,
        ]);
    }

    private function tx(string $type, float $amount, string $date): void
    {
        Transaction::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'type' => $type,
            'amount' => $amount,
            'currency' => 'ARS',
            'date' => $date,
        ]);
    }

    public function test_daily_spending_includes_income_and_cumulative(): void
    {
        // Dos días distintos del mes en curso
        $day1 = now()->startOfMonth()->toDateString();
        $day2 = now()->startOfMonth()->addDay()->toDateString();

        $this->tx('expense', 1000, $day1);
        $this->tx('expense', 500, $day2);
        $this->tx('income', 3000, $day1);

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.index'));

        $response->assertStatus(200);

        $dailySpending = collect($response->viewData('dailySpending'));

        // Cada fila debe tener las nuevas claves
        $first = $dailySpending->first();
        $this->assertArrayHasKey('cumulative', $first);
        $this->assertArrayHasKey('income_amount', $first);
        $this->assertArrayHasKey('income_cumulative', $first);

        // Día 1: gasto 1000, ingreso 3000
        $d1 = $dailySpending->firstWhere('day', 1);
        $this->assertSame(1000.0, (float) $d1['amount']);
        $this->assertSame(1000.0, (float) $d1['cumulative']);
        $this->assertSame(3000.0, (float) $d1['income_amount']);
        $this->assertSame(3000.0, (float) $d1['income_cumulative']);

        // Día 2: gasto 500 → acumulado 1500; ingreso 0 → acumulado sigue 3000
        $d2 = $dailySpending->firstWhere('day', 2);
        $this->assertSame(500.0, (float) $d2['amount']);
        $this->assertSame(1500.0, (float) $d2['cumulative']);
        $this->assertSame(0.0, (float) $d2['income_amount']);
        $this->assertSame(3000.0, (float) $d2['income_cumulative']);
    }
}
