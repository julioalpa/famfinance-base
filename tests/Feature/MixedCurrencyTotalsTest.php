<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ExchangeRate;
use App\Models\FamilyGroup;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Cubre la corrección de los cálculos de suma que mezclaban ARS y USD sin
 * convertir. Todo debe valorizarse en ARS usando la tasa vigente a la fecha
 * de cada movimiento.
 */
class MixedCurrencyTotalsTest extends TestCase
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

    private function rate(string $date, float $rate): void
    {
        ExchangeRate::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'from_currency' => 'USD',
            'to_currency' => 'ARS',
            'rate' => $rate,
            'date' => $date,
        ]);
    }

    private function account(string $type = 'cash', string $currency = 'ARS'): Account
    {
        return Account::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'name' => ucfirst($type), 'type' => $type, 'currency' => $currency,
            'initial_balance' => 0, 'is_active' => true,
        ]);
    }

    private function tx(Account $acc, string $type, float $amount, string $currency, string $date): Transaction
    {
        return Transaction::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $acc->id, 'type' => $type, 'amount' => $amount,
            'currency' => $currency, 'date' => $date,
        ]);
    }

    public function test_account_balance_converts_usd_movements_to_native_currency(): void
    {
        // Tasa: 1 USD = 1000 ARS a la fecha del movimiento USD.
        $this->rate('2026-01-01', 1000);

        $cash = $this->account('cash', 'ARS');
        $this->tx($cash, 'income', 5000, 'ARS', '2026-01-10');  // +5.000 ARS
        $this->tx($cash, 'income', 10, 'USD', '2026-01-15');    // +10 USD → +10.000 ARS
        $this->tx($cash, 'expense', 2000, 'ARS', '2026-01-20'); // -2.000 ARS

        // 5.000 + 10.000 - 2.000 = 13.000 ARS (sin convertir daría 3.010).
        $this->assertSame(13000.0, $cash->fresh()->balance);
    }

    public function test_account_balance_uses_rate_at_movement_date(): void
    {
        $this->rate('2026-01-01', 1000);
        $this->rate('2026-06-01', 1500);

        $cash = $this->account('cash', 'ARS');
        $this->tx($cash, 'income', 10, 'USD', '2026-02-10'); // 10 × 1000 = 10.000
        $this->tx($cash, 'income', 10, 'USD', '2026-07-10'); // 10 × 1500 = 15.000

        $this->assertSame(25000.0, $cash->fresh()->balance);
    }

    public function test_transactions_month_stats_convert_mixed_currencies(): void
    {
        $this->rate('2026-03-01', 1200);

        $acc = $this->account('cash', 'ARS');
        $this->tx($acc, 'expense', 3000, 'ARS', '2026-03-05');  // 3.000
        $this->tx($acc, 'expense', 5, 'USD', '2026-03-10');     // 5 × 1200 = 6.000
        $this->tx($acc, 'income', 2, 'USD', '2026-03-12');      // 2 × 1200 = 2.400

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('transactions.index', ['month' => '2026-03']))
            ->assertStatus(200);

        $stats = $response->viewData('monthStats');

        $this->assertSame(9000.0, (float) $stats['expenses']); // 3.000 + 6.000
        $this->assertSame(2400.0, (float) $stats['income']);
        $this->assertSame(-6600.0, (float) $stats['balance']);
    }

    public function test_tag_totals_include_converted_usd_transactions(): void
    {
        $this->rate('2026-04-01', 1000);

        $acc = $this->account('cash', 'ARS');
        $tag = Tag::create([
            'family_group_id' => $this->group->id,
            'name' => 'Viajes', 'color' => '#ff0000',
        ]);

        $t1 = $this->tx($acc, 'expense', 1000, 'ARS', '2026-04-05');
        $t2 = $this->tx($acc, 'expense', 10, 'USD', '2026-04-06'); // 10 × 1000 = 10.000
        $t1->tags()->attach($tag->id);
        $t2->tags()->attach($tag->id);

        $response = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('tags.index'))
            ->assertStatus(200);

        $tags = collect($response->viewData('tags'));
        $viajes = $tags->firstWhere('name', 'Viajes');

        // 1.000 ARS + 10.000 ARS (USD convertido) = 11.000, no 1.000.
        $this->assertSame(11000.0, (float) $viajes->tx_amount);
    }
}
