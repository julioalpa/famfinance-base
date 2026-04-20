<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // quien registró
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();    // cuenta afectada
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('type', ['expense', 'income', 'transfer']); // gasto, ingreso, transferencia

            // Origen del ingreso (solo si type = income)
            $table->enum('income_source', ['salary', 'credit', 'cash', 'loan', 'other'])->nullable();

            $table->decimal('amount', 15, 2);         // monto total de la transacción
            $table->enum('currency', ['ARS', 'USD'])->default('ARS');
            $table->date('date');
            $table->string('description')->nullable();

            // Cuotas (solo para gastos con tarjeta de crédito)
            $table->boolean('has_installments')->default(false);
            $table->unsignedTinyInteger('installments_count')->nullable(); // total de cuotas
            $table->decimal('installment_amount', 15, 2)->nullable();      // monto por cuota

            // Para transferencias entre cuentas
            $table->foreignId('target_account_id')->nullable()->constrained('accounts')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['family_group_id', 'date']);
            $table->index(['account_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
