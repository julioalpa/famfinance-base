<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntThresholdTest extends TestCase
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

    private function expense(float $amount): void
    {
        Transaction::create([
            'family_group_id' => $this->group->id, 'user_id' => $this->user->id,
            'account_id' => $this->account->id, 'type' => 'expense', 'amount' => $amount,
            'currency' => 'ARS', 'date' => now()->format('Y-m-d'),
        ]);
    }

    public function test_default_threshold_is_2000(): void
    {
        $this->expense(1500); // hormiga con default 2000
        $this->expense(3000); // no hormiga

        $res = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.monthly', ['mes' => now()->format('Y-m')]))
            ->assertStatus(200);

        $this->assertSame(2000.0, (float) $res->viewData('antThreshold'));
        $this->assertSame(1500.0, (float) $res->viewData('antTotal'));
        $this->assertSame(1, $res->viewData('antCount'));
    }

    public function test_updating_threshold_changes_report(): void
    {
        $this->expense(1500);
        $this->expense(3000);

        // Subir el umbral a 5000 → ambos gastos pasan a ser hormiga.
        $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id, '_token' => 'tk'])
            ->post(route('family-groups.settings'), ['_token' => 'tk', 'ant_threshold' => 5000])
            ->assertRedirect();

        $this->assertSame('5000.00', (string) $this->group->fresh()->ant_threshold);

        $res = $this->actingAs($this->user)
            ->withSession(['active_family_group_id' => $this->group->id])
            ->get(route('reports.monthly', ['mes' => now()->format('Y-m')]))
            ->assertStatus(200);

        $this->assertSame(5000.0, (float) $res->viewData('antThreshold'));
        $this->assertSame(4500.0, (float) $res->viewData('antTotal')); // 1500 + 3000
        $this->assertSame(2, $res->viewData('antCount'));
    }
}
