<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('recurring_expense_id')
                  ->nullable()
                  ->constrained('recurring_expenses')
                  ->nullOnDelete()
                  ->after('notes');
        });

        Schema::create('recurring_expense_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurring_expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['confirmed', 'skipped']);
            $table->timestamps();

            $table->unique(['recurring_expense_id', 'month', 'year']);
            $table->index(['family_group_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\RecurringExpense::class);
            $table->dropColumn('recurring_expense_id');
        });

        Schema::dropIfExists('recurring_expense_logs');
    }
};
