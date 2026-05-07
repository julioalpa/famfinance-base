<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Stores the resulting account balance after an adjustment transaction,
            // used as a checkpoint so edits to prior movements don't shift the final balance.
            $table->decimal('balance_snapshot', 15, 2)->nullable()->after('adjustment_direction');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('balance_snapshot');
        });
    }
};
