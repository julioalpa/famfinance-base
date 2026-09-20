<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Presupuesto mensual por categoría, por grupo familiar.
     *
     * Es un límite recurrente (aplica a todos los meses): cada mes se compara
     * el gasto real de la categoría contra este monto. Un grupo puede fijar
     * presupuesto tanto para categorías propias como del sistema.
     */
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('currency', ['ARS', 'USD'])->default('ARS');
            $table->timestamps();

            // Un único presupuesto por categoría dentro de cada grupo.
            $table->unique(['family_group_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
