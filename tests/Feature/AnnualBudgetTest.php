<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnualBudgetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);

        $this->user = User::create(['name' => 'T', 'email' => 't@e.com', 'password' => bcrypt('x')]);
        $this->group = FamilyGroup::create(['name' => 'G', 'owner_id' => $this->user->id]);
        $this->group->members()->attach($this->user->id, ['role' => 'owner', 'joined_at' => now()]);
        $this->account = Account::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id, 'name' => 'Ef',
            'type' => 'cash', 'currency' => 'ARS', 'initial_balance' => 0, 'is_active' => true,
        ]);
    }

    private function category(string $name): Category
    {
        return Category::create(['family_group_id' => $this->group->id, 'name' => $name, 'type' => 'expense', 'is_system' => false]);
    }

    private function spend(int $catId, float $amount, string $date): void
    {
        Transaction::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $this->account->id, 'category_id' => $catId, 'type' => 'expense',
            'amount' => $amount, 'currency' => 'ARS', 'date' => $date,
        ]);
    }

    private function budgetsView(string $month)
    {
        return $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('budgets.index', ['month' => $month]))
            ->assertStatus(200);
    }

    public function test_annual_budget_accumulates_over_the_whole_year(): void
    {
        $cat = $this->category('Vacaciones');
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 100000, 'currency' => 'ARS', 'period' => 'annual']);

        // Gastos en distintos meses del mismo año.
        $this->spend($cat->id, 30000, '2026-01-10');
        $this->spend($cat->id, 40000, '2026-07-20');
        // Gasto del año anterior: no debe contar.
        $this->spend($cat->id, 99000, '2025-12-30');

        // Visualizando marzo 2026, el anual debe acumular ene+jul = 70000 (75% no,
        // 70%), independientemente del mes visto.
        $res = $this->budgetsView('2026-03');
        $annual = collect($res->viewData('annualBudgets'))->firstWhere('budget_id', Budget::first()->id);

        $this->assertNotNull($annual);
        $this->assertSame(70000.0, (float) $annual['spent']);
        $this->assertSame(100000.0, (float) $annual['limit_ars']);
        $this->assertSame(70.0, (float) $annual['pct']);
        // No aparece entre los mensuales.
        $this->assertTrue(collect($res->viewData('monthlyBudgets'))->isEmpty());
    }

    public function test_monthly_budget_still_isolates_by_month(): void
    {
        $cat = $this->category('Super');
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => 50000, 'currency' => 'ARS', 'period' => 'monthly']);

        $this->spend($cat->id, 20000, '2026-03-10');
        $this->spend($cat->id, 45000, '2026-04-10'); // otro mes

        $res = $this->budgetsView('2026-03');
        $monthly = collect($res->viewData('monthlyBudgets'))->firstWhere('budget_id', Budget::first()->id);

        $this->assertSame(20000.0, (float) $monthly['spent']); // sólo marzo
    }

    public function test_upsert_persists_period(): void
    {
        $cat = $this->category('Viajes');

        $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id, '_token' => 'tk'])
            ->post(route('budgets.upsert'), ['_token' => 'tk', 'category_id' => $cat->id, 'amount' => 200000, 'currency' => 'ARS', 'period' => 'annual'])
            ->assertRedirect();

        $this->assertDatabaseHas('budgets', [
            'category_id' => $cat->id,
            'period' => 'annual',
            'amount' => 200000,
        ]);
    }
}
