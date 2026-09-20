<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FamilyGroup;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallmentDueDayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private FamilyGroup $group;

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
    }

    private function creditAccount(int $closingDay, ?int $dueDay): Account
    {
        return Account::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'name' => 'Visa',
            'type' => 'credit',
            'currency' => 'ARS',
            'closing_day' => $closingDay,
            'due_day' => $dueDay,
            'initial_balance' => 0,
            'is_active' => true,
        ]);
    }

    private function createInstallmentTx(Account $account, string $date, int $count, float $amount = 12000): void
    {
        app(TransactionService::class)->create([
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => $amount,
            'currency' => 'ARS',
            'date' => $date,
            'has_installments' => true,
            'installments_count' => $count,
        ], $this->group->id, $this->user->id);
    }

    public function test_installments_use_card_due_day(): void
    {
        // Cierre día 20, vencimiento día 10. Compra el 5 (antes del cierre) →
        // primera cuota en el mismo mes.
        $account = $this->creditAccount(20, 10);
        $this->createInstallmentTx($account, '2026-03-05', 3);

        $inst = Installment::where('account_id', $account->id)
            ->orderBy('installment_number')
            ->get();

        $this->assertCount(3, $inst);
        // Todas vencen el día 10, meses consecutivos desde marzo.
        $this->assertSame('2026-03-10', $inst[0]->due_date->format('Y-m-d'));
        $this->assertSame('2026-04-10', $inst[1]->due_date->format('Y-m-d'));
        $this->assertSame('2026-05-10', $inst[2]->due_date->format('Y-m-d'));
    }

    public function test_purchase_after_closing_shifts_to_next_month(): void
    {
        // Cierre día 20, vencimiento día 10. Compra el 25 (después del cierre)
        // → primera cuota el mes siguiente.
        $account = $this->creditAccount(20, 10);
        $this->createInstallmentTx($account, '2026-03-25', 2);

        $inst = Installment::where('account_id', $account->id)
            ->orderBy('installment_number')
            ->get();

        $this->assertSame('2026-04-10', $inst[0]->due_date->format('Y-m-d'));
        $this->assertSame('2026-05-10', $inst[1]->due_date->format('Y-m-d'));
    }

    public function test_due_day_clamped_to_month_length(): void
    {
        // Vencimiento día 31: en meses cortos se acota al último día.
        $account = $this->creditAccount(5, 31);
        // Compra el 10 (después del cierre día 5) → primera cuota mes siguiente (febrero).
        $this->createInstallmentTx($account, '2026-01-10', 3);

        $inst = Installment::where('account_id', $account->id)
            ->orderBy('installment_number')
            ->get();

        // Febrero 2026 no es bisiesto → 28. Marzo → 31. Abril → 30.
        $this->assertSame('2026-02-28', $inst[0]->due_date->format('Y-m-d'));
        $this->assertSame('2026-03-31', $inst[1]->due_date->format('Y-m-d'));
        $this->assertSame('2026-04-30', $inst[2]->due_date->format('Y-m-d'));
    }

    public function test_no_due_day_falls_back_to_day_one(): void
    {
        $account = $this->creditAccount(20, null);
        $this->createInstallmentTx($account, '2026-03-05', 2);

        $inst = Installment::where('account_id', $account->id)
            ->orderBy('installment_number')
            ->get();

        $this->assertSame('2026-03-01', $inst[0]->due_date->format('Y-m-d'));
        $this->assertSame('2026-04-01', $inst[1]->due_date->format('Y-m-d'));
    }

    public function test_data_migration_fixes_existing_day_one_installments(): void
    {
        $account = $this->creditAccount(20, 15);

        $tx = Transaction::create([
            'family_group_id' => $this->group->id,
            'user_id' => $this->user->id,
            'account_id' => $account->id,
            'type' => 'expense',
            'amount' => 2000,
            'currency' => 'ARS',
            'date' => '2026-03-05',
        ]);

        // Simular cuotas "viejas" con due_date en el día 1 (estado previo al fix).
        $ins1 = Installment::create([
            'transaction_id' => $tx->id,
            'account_id' => $account->id,
            'installment_number' => 1,
            'amount' => 1000,
            'due_date' => '2026-03-01',
            'is_paid' => false,
        ]);
        $ins2 = Installment::create([
            'transaction_id' => $tx->id,
            'account_id' => $account->id,
            'installment_number' => 2,
            'amount' => 1000,
            'due_date' => '2026-04-01',
            'is_paid' => false,
        ]);

        // Ejecutar la lógica de la migración de corrección.
        $migration = require database_path('migrations/2026_09_20_000002_fix_installments_due_day.php');
        $migration->up();

        $this->assertSame('2026-03-15', $ins1->fresh()->due_date->format('Y-m-d'));
        $this->assertSame('2026-04-15', $ins2->fresh()->due_date->format('Y-m-d'));
    }
}
