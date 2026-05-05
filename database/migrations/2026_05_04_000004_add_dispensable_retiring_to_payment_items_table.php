<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->boolean('is_dispensable')->default(false)->after('notes');
            $table->boolean('is_retiring')->default(false)->after('is_dispensable');
        });
    }

    public function down(): void
    {
        Schema::table('payment_items', function (Blueprint $table) {
            $table->dropColumn(['is_dispensable', 'is_retiring']);
        });
    }
};
