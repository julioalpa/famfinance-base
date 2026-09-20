<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Corrige el día de vencimiento de las cuotas existentes.
     *
     * Hasta ahora todas las cuotas se generaban con due_date en el día 1 del
     * mes. Este ajuste las mueve al `due_day` real de la tarjeta (acotado al
     * último día del mes), preservando el mes/año. Las cuotas de tarjetas sin
     * `due_day` configurado quedan como están (día 1).
     */
    public function up(): void
    {
        // Mapa account_id => due_day (sólo tarjetas con due_day definido).
        $dueDays = DB::table('accounts')
            ->whereNotNull('due_day')
            ->where('due_day', '>', 0)
            ->pluck('due_day', 'id');

        if ($dueDays->isEmpty()) {
            return;
        }

        DB::table('installments')
            ->whereIn('account_id', $dueDays->keys())
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($dueDays) {
                foreach ($rows as $inst) {
                    $dueDay = (int) $dueDays->get($inst->account_id);
                    if ($dueDay < 1) {
                        continue;
                    }

                    $base = Carbon::parse($inst->due_date)->startOfMonth();
                    $dueOnMonth = min($dueDay, $base->daysInMonth);
                    $newDate = $base->day($dueOnMonth)->format('Y-m-d');

                    if ($newDate !== $inst->due_date) {
                        DB::table('installments')
                            ->where('id', $inst->id)
                            ->update(['due_date' => $newDate]);
                    }
                }
            });
    }

    /**
     * Revierte al día 1 del mes (estado previo).
     */
    public function down(): void
    {
        DB::table('installments')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $inst) {
                DB::table('installments')
                    ->where('id', $inst->id)
                    ->update([
                        'due_date' => Carbon::parse($inst->due_date)->startOfMonth()->format('Y-m-d'),
                    ]);
            }
        });
    }
};
