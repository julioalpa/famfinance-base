<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income','expense','transfer','adjustment') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // SQLite enforces type values at the application layer; no constraint change needed.
        } else {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_type_check");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('income','expense','transfer','adjustment'))");
        }

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('adjustment_direction', ['in', 'out'])->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('adjustment_direction');
        });

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('income','expense','transfer') NOT NULL");
        } elseif ($driver === 'sqlite') {
            // No constraint to revert in SQLite.
        } else {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_type_check");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_type_check CHECK (type IN ('income','expense','transfer'))");
        }
    }
};
