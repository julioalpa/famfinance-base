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

class BudgetAlertTest extends TestCase
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

    private function budgetWithSpend(string $catName, float $limit, float $spent): void
    {
        $cat = Category::create(['family_group_id' => $this->group->id, 'name' => $catName, 'type' => 'expense', 'is_system' => false]);
        Budget::create(['family_group_id' => $this->group->id, 'category_id' => $cat->id, 'amount' => $limit, 'currency' => 'ARS', 'period' => 'monthly']);
        if ($spent > 0) {
            Transaction::create([
                'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
                'account_id' => $this->account->id, 'category_id' => $cat->id, 'type' => 'expense',
                'amount' => $spent, 'currency' => 'ARS', 'date' => now()->format('Y-m-d'),
            ]);
        }
    }

    private function dashboard()
    {
        return $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get('/')
            ->assertStatus(200);
    }

    public function test_alerts_include_over_and_warn_but_not_ok(): void
    {
        $this->budgetWithSpend('Excedido', 10000, 12000);   // 120% → over
        $this->budgetWithSpend('CercaLimite', 10000, 8500); // 85%  → warn
        $this->budgetWithSpend('EnRegla', 10000, 3000);     // 30%  → ok

        $res = $this->dashboard();
        $alerts = collect($res->viewData('budgetAlerts'));
        $names = $alerts->map(fn ($a) => $a['category']->name)->all();

        $this->assertContains('Excedido', $names);
        $this->assertContains('CercaLimite', $names);
        $this->assertNotContains('EnRegla', $names);

        $this->assertSame(1, $res->viewData('budgetOverCount'));
        $this->assertSame(1, $res->viewData('budgetWarnCount'));

        $res->assertSee('Presupuesto excedido', false);
        $res->assertSee('Cerca del límite', false);
    }

    public function test_no_alerts_when_all_within_budget(): void
    {
        $this->budgetWithSpend('A', 10000, 1000);
        $this->budgetWithSpend('B', 10000, 5000);

        $res = $this->dashboard();
        $this->assertTrue(collect($res->viewData('budgetAlerts'))->isEmpty());
        $res->assertDontSee('Presupuesto excedido', false);
    }

    public function test_alerts_count_full_set_not_only_top_five(): void
    {
        // 6 presupuestos excedidos → el conteo debe ser 6 (no 5 del widget).
        for ($i = 1; $i <= 6; $i++) {
            $this->budgetWithSpend("Cat{$i}", 1000, 2000);
        }

        $res = $this->dashboard();
        $this->assertSame(6, $res->viewData('budgetOverCount'));
        $this->assertCount(6, collect($res->viewData('budgetAlerts')));
    }
}
