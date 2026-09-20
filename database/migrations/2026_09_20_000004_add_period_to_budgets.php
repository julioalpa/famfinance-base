<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Período del presupuesto:
     *  - monthly: límite por mes (default, comportamiento previo).
     *  - annual:  límite para todo el año calendario (ej. Vacaciones), el gasto
     *             se acumula de enero a diciembre del año visualizado.
     */
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->enum('period', ['monthly', 'annual'])->default('monthly')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn('period');
        });
    }
};
