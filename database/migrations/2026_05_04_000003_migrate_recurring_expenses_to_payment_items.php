<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now()->toDateTimeString();

        $expenses = DB::table('recurring_expenses')
            ->whereNull('deleted_at')
            ->get();

        foreach ($expenses as $expense) {
            $paymentItemId = DB::table('payment_items')->insertGetId([
                'family_group_id' => $expense->family_group_id,
                'account_id'      => $expense->account_id,
                'category_id'     => $expense->category_id,
                'description'     => $expense->description,
                'currency'        => $expense->currency,
                'day_of_month'    => $expense->day_of_month,
                'is_active'       => $expense->is_active,
                'is_direct_debit' => true,
                'amount'          => $expense->amount,
                'notes'           => $expense->notes,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);

            $logs = DB::table('recurring_expense_logs')
                ->where('recurring_expense_id', $expense->id)
                ->get();

            foreach ($logs as $log) {
                $exists = DB::table('monthly_payments')
                    ->where('payment_item_id', $paymentItemId)
                    ->where('month', $log->month)
                    ->where('year', $log->year)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $data = [
                    'payment_item_id' => $paymentItemId,
                    'family_group_id' => $log->family_group_id,
                    'month'           => $log->month,
                    'year'            => $log->year,
                    'is_paid'         => $log->status === 'confirmed',
                    'is_dismissed'    => $log->status === 'skipped',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                if ($log->status === 'confirmed' && $log->transaction_id) {
                    $tx = DB::table('transactions')->find($log->transaction_id);
                    $data['amount']         = $tx?->amount;
                    $data['paid_at']        = $tx?->created_at;
                    $data['transaction_id'] = $log->transaction_id;
                }

                DB::table('monthly_payments')->insert($data);
            }
        }
    }

    public function down(): void
    {
        // Remove only the payment_items created by this migration (is_direct_debit = true)
        DB::table('payment_items')->where('is_direct_debit', true)->delete();
    }
};
