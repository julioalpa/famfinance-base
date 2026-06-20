<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->boolean('transaction_was_created_here')
                ->default(true)
                ->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_payments', function (Blueprint $table) {
            $table->dropColumn('transaction_was_created_here');
        });
    }
};
