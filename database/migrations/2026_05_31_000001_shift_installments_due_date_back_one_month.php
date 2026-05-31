<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('installments')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $inst) {
                DB::table('installments')
                    ->where('id', $inst->id)
                    ->update([
                        'due_date' => Carbon::parse($inst->due_date)->subMonth()->format('Y-m-d'),
                    ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('installments')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $inst) {
                DB::table('installments')
                    ->where('id', $inst->id)
                    ->update([
                        'due_date' => Carbon::parse($inst->due_date)->addMonth()->format('Y-m-d'),
                    ]);
            }
        });
    }
};
