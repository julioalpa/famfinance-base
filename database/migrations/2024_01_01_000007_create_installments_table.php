<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Cada fila representa UNA cuota de una transacción en cuotas.
         * Se generan automáticamente al crear una transacción con has_installments = true.
         * Esto permite calcular mes a mes cuánto hay que pagar en cada tarjeta.
         */
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();  // cuenta de crédito
            $table->unsignedTinyInteger('installment_number');  // nro de cuota (1, 2, 3...)
            $table->decimal('amount', 15, 2);
            $table->date('due_date');    // fecha estimada de débito en el resumen
            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->index(['account_id', 'due_date']);
            $table->index(['transaction_id', 'installment_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
