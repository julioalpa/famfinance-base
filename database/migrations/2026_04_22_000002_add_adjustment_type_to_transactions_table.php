<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income','expense','transfer','adjustment') NOT NULL");

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('adjustment_direction', ['in', 'out'])->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('adjustment_direction');
        });

        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income','expense','transfer') NOT NULL");
    }
};
