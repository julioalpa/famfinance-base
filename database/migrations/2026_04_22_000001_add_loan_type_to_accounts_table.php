<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','digital','credit','loan') NOT NULL");
        } else {
            DB::statement("ALTER TABLE accounts DROP CONSTRAINT IF EXISTS accounts_type_check");
            DB::statement("ALTER TABLE accounts ADD CONSTRAINT accounts_type_check CHECK (type IN ('cash','digital','credit','loan'))");
        }

        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('initial_balance', 15, 2)->nullable()->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            //$table->dropColumn('initial_balance');
        });

        //DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','digital','credit') NOT NULL");
    }
};
