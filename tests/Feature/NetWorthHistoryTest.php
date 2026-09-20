<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class NetWorthHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create(['name' => 'T', 'email' => 't@e.com', 'password' => bcrypt('x')]);
        $this->group = FamilyGroup::create(['name' => 'G', 'owner_id' => $this->user->id]);
        $this->group->members()->attach($this->user->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function account(string $type, string $currency = 'ARS', float $initial = 0): Account
    {
        return Account::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'name' => ucfirst($type), 'type' => $type, 'currency' => $currency,
            'initial_balance' => $initial, 'is_active' => true,
        ]);
    }

    private function tx(Account $acc, string $type, float $amount, string $date): void
    {
        Transaction::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $acc->id, 'type' => $type, 'amount' => $amount,
            'currency' => $acc->currency, 'date' => $date,
        ]);
    }

    public function test_balance_as_of_only_counts_movements_up_to_date(): void
    {
        $cash = $this->account('cash');
        $this->tx($cash, 'income', 1000, '2026-01-10');
        $this->tx($cash, 'expense', 300, '2026-02-15');
        $this->tx($cash, 'income', 500, '2026-03-05');

        // A fin de enero: sólo el ingreso de 1000.
        $this->assertSame(1000.0, $cash->balanceAsOf(Carbon::parse('2026-01-31')));
        // A fin de febrero: 1000 - 300 = 700.
        $this->assertSame(700.0, $cash->balanceAsOf(Carbon::parse('2026-02-28')));
        // A fin de marzo: 700 + 500 = 1200 (igual al saldo actual).
        $this->assertSame(1200.0, $cash->balanceAsOf(Carbon::parse('2026-03-31')));
    }

    public function test_balance_as_of_respects_snapshot_cutoff(): void
    {
        $cash = $this->account('cash');
        // Movimiento viejo + ajuste con snapshot que congela el saldo en 5000.
        $this->tx($cash, 'income', 999, '2026-01-05');
        Transaction::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $cash->id, 'type' => 'adjustment', 'adjustment_direction' => 'in',
            'amount' => 0, 'currency' => 'ARS', 'date' => '2026-02-01', 'balance_snapshot' => 5000,
        ]);
        $this->tx($cash, 'expense', 1000, '2026-02-20');

        // A fin de febrero: snapshot 5000 - 1000 = 4000 (ignora el ingreso viejo).
        $this->assertSame(4000.0, $cash->balanceAsOf(Carbon::parse('2026-02-28')));
    }

    public function test_report_exposes_net_worth_history(): void
    {
        $cash = $this->account('cash');
        $credit = $this->account('credit');

        $this->tx($cash, 'income', 10000, now()->startOfMonth()->toDateString());
        // Gasto con tarjeta de crédito → aumenta el pasivo.
        $this->tx($credit, 'expense', 3000, now()->startOfMonth()->addDay()->toDateString());

        $html = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.index'))
            ->assertStatus(200);

        $history = collect($html->viewData('netWorthHistory'));
        $this->assertNotEmpty($history);

        // El último mes: activos 10000, pasivos 3000, neto 7000.
        $last = $history->last();
        $this->assertSame(10000.0, (float) $last['assets']);
        $this->assertSame(3000.0, (float) $last['liabilities']);
        $this->assertSame(7000.0, (float) $last['net']);
    }
}
