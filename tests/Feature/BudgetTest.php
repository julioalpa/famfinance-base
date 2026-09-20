<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
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

    private function category(string $name = 'Supermercado'): Category
    {
        return Category::create([
            'family_group_id' => $this->group->id,
            'name' => $name,
            'type' => 'expense',
            'is_system' => false,
        ]);
    }

    private function spend(int $categoryId, float $amount, string $date): void
    {
        Transaction::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'account_id' => $this->account->id,
            'category_id' => $categoryId,
            'type' => 'expense',
            'amount' => $amount,
            'currency' => 'ARS',
            'date' => $date,
        ]);
    }

    private function acting()
    {
        return $this->actingAs($this->user)
            ->withSession([
                'active_family_group_id' => $this->group->id,
                '_token' => 'test-token',
            ]);
    }

    public function test_upsert_creates_budget(): void
    {
        $cat = $this->category();

        $this->acting()->post(route('budgets.upsert'), [
            '_token' => 'test-token',
            'category_id' => $cat->id,
            'amount' => 50000,
            'currency' => 'ARS',
        ])->assertRedirect();

        $this->assertDatabaseHas('budgets', [
            'family_group_id' => $this->group->id,
            'category_id' => $cat->id,
            'amount' => 50000,
        ]);
    }

    public function test_upsert_with_zero_amount_deletes_budget(): void
    {
        $cat = $this->category();
        Budget::create([
            'family_group_id' => $this->group->id,
            'category_id' => $cat->id,
            'amount' => 30000,
            'currency' => 'ARS',
        ]);

        $this->acting()->post(route('budgets.upsert'), [
            '_token' => 'test-token',
            'category_id' => $cat->id,
            'amount' => 0,
        ])->assertRedirect();

        $this->assertDatabaseMissing('budgets', ['category_id' => $cat->id]);
    }

    public function test_index_shows_ok_status_under_80_pct(): void
    {
        $cat = $this->category();
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 100000, 'currency' => 'ARS']);
        $this->spend($cat->id, 50000, now()->format('Y-m-d')); // 50%

        $response = $this->acting()->get(route('budgets.index', ['month' => now()->format('Y-m')]));
        $response->assertStatus(200);
        // Barra en estado ok
        $response->assertSee('bdg-fill-ok', false);
        $response->assertSee('50%', false);
    }

    public function test_index_shows_warn_status_between_80_and_99(): void
    {
        $cat = $this->category();
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 100000, 'currency' => 'ARS']);
        $this->spend($cat->id, 85000, now()->format('Y-m-d')); // 85%

        $response = $this->acting()->get(route('budgets.index', ['month' => now()->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('bdg-fill-warn', false);
    }

    public function test_index_shows_over_status_at_or_above_100(): void
    {
        $cat = $this->category();
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 100000, 'currency' => 'ARS']);
        $this->spend($cat->id, 120000, now()->format('Y-m-d')); // 120%

        $response = $this->acting()->get(route('budgets.index', ['month' => now()->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('bdg-fill-over', false);
        $response->assertSee('Excedido', false);
    }

    public function test_spending_from_other_month_is_not_counted(): void
    {
        $cat = $this->category();
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 100000, 'currency' => 'ARS']);
        // Gasto del mes pasado: no debe contar en el mes actual
        $this->spend($cat->id, 90000, now()->subMonthNoOverflow()->format('Y-m-15'));
        // Gasto del mes actual
        $this->spend($cat->id, 20000, now()->format('Y-m-d')); // 20%

        $response = $this->acting()->get(route('budgets.index', ['month' => now()->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('bdg-fill-ok', false);
        $response->assertSee('20%', false);
    }

    public function test_destroy_rejects_other_group_budget(): void
    {
        $otherUser = User::create(['name' => 'Otro', 'email' => 'otro@example.com', 'password' => bcrypt('x')]);
        $otherGroup = FamilyGroup::create(['name' => 'Otra Familia', 'owner_id' => $otherUser->id]);
        $otherCat = Category::create(['family_group_id' => $otherGroup->id, 'name' => 'X', 'type' => 'expense', 'is_system' => false]);
        $otherBudget = Budget::create(['family_group_id' => $otherGroup->id, 'category_id' => $otherCat->id, 'amount' => 1000, 'currency' => 'ARS']);

        $this->acting()->delete(route('budgets.destroy', $otherBudget), ['_token' => 'test-token'])->assertForbidden();

        $this->assertDatabaseHas('budgets', ['id' => $otherBudget->id]);
    }
}
