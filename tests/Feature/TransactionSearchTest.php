<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create(['name' => 'T', 'email' => 't@e.com', 'password' => bcrypt('x')]);
        $this->group = FamilyGroup::create(['name' => 'G', 'owner_id' => $this->user->id]);
        $this->group->members()->attach($this->user->id, ['role' => 'owner', 'joined_at' => now()]);
        $this->account = Account::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id, 'name' => 'Ef',
            'type' => 'cash', 'currency' => 'ARS', 'initial_balance' => 0, 'is_active' => true,
        ]);
    }

    private function tx(array $attrs): Transaction
    {
        return Transaction::create(array_merge([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $this->account->id, 'type' => 'expense', 'currency' => 'ARS',
        ], $attrs));
    }

    private function visit(array $params)
    {
        return $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('transactions.index', $params));
    }

    private function descriptions($response): array
    {
        return collect($response->viewData('transactions')->items())
            ->pluck('description')->all();
    }

    public function test_text_search_matches_description(): void
    {
        $this->tx(['description' => 'Supermercado Coto', 'amount' => 1000, 'date' => '2026-03-10']);
        $this->tx(['description' => 'Nafta YPF', 'amount' => 2000, 'date' => '2026-03-11']);
        $this->tx(['description' => 'Farmacia', 'amount' => 500, 'date' => '2026-03-12', 'notes' => 'compra en Coto express']);

        $res = $this->visit(['q' => 'Coto'])->assertStatus(200);
        $desc = $this->descriptions($res);

        $this->assertContains('Supermercado Coto', $desc);   // match en descripción
        $this->assertContains('Farmacia', $desc);            // match en notas
        $this->assertNotContains('Nafta YPF', $desc);
    }

    public function test_date_range_filter_overrides_month(): void
    {
        $this->tx(['description' => 'Enero', 'amount' => 100, 'date' => '2026-01-15']);
        $this->tx(['description' => 'Marzo', 'amount' => 100, 'date' => '2026-03-15']);
        $this->tx(['description' => 'Mayo', 'amount' => 100, 'date' => '2026-05-15']);

        // Aunque el mes por defecto sería el actual, el rango manda.
        $res = $this->visit(['from' => '2026-02-01', 'to' => '2026-04-30'])->assertStatus(200);
        $desc = $this->descriptions($res);

        $this->assertSame(['Marzo'], $desc);
    }

    public function test_amount_range_filter(): void
    {
        $this->tx(['description' => 'Chico', 'amount' => 500, 'date' => '2026-03-10']);
        $this->tx(['description' => 'Medio', 'amount' => 5000, 'date' => '2026-03-11']);
        $this->tx(['description' => 'Grande', 'amount' => 50000, 'date' => '2026-03-12']);

        $res = $this->visit(['min' => 1000, 'max' => 10000, 'from' => '2026-03-01', 'to' => '2026-03-31'])->assertStatus(200);
        $desc = $this->descriptions($res);

        $this->assertSame(['Medio'], $desc);
    }

    public function test_filtered_total_and_count_reflect_filters(): void
    {
        $this->tx(['description' => 'A', 'amount' => 1000, 'date' => '2026-03-10']);
        $this->tx(['description' => 'B', 'amount' => 3000, 'date' => '2026-03-11']);
        $this->tx(['description' => 'C otro', 'amount' => 9999, 'date' => '2026-03-12']);

        $res = $this->visit(['q' => 'otro'])->assertStatus(200);

        $this->assertSame(1, $res->viewData('filteredCount'));
        $this->assertSame(9999.0, (float) $res->viewData('filteredTotal'));
    }

    public function test_member_filter(): void
    {
        $other = User::create(['name' => 'Pareja', 'email' => 'p@e.com', 'password' => bcrypt('x')]);
        $this->group->members()->attach($other->id, ['role' => 'member', 'joined_at' => now()]);

        $this->tx(['description' => 'Mío', 'amount' => 100, 'date' => '2026-03-10', 'user_id' => $this->user->id]);
        $this->tx(['description' => 'De la pareja', 'amount' => 200, 'date' => '2026-03-11', 'user_id' => $other->id]);

        $res = $this->visit(['user_id' => $other->id, 'from' => '2026-03-01', 'to' => '2026-03-31'])->assertStatus(200);
        $this->assertSame(['De la pareja'], $this->descriptions($res));
    }
}
