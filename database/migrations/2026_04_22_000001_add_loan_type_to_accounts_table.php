<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','digital','credit','loan') NOT NULL");

        Schema::table('accounts', function (Blueprint $table) {
            $table->decimal('initial_balance', 15, 2)->nullable()->after('credit_limit');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('initial_balance');
        });

        DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','digital','credit') NOT NULL");
    }
};
