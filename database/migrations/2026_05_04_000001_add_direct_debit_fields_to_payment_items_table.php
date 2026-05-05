<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->boolean('is_direct_debit')->default(false)->after('is_active');
            $table->decimal('amount', 15, 2)->nullable()->after('is_direct_debit');
            $table->text('notes')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->dropColumn(['is_direct_debit', 'amount', 'notes']);
        });
    }
};
