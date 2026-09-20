<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Umbral configurable de "gastos hormiga" (en ARS) por grupo familiar.
     * Los gastos por debajo de este monto se consideran hormiga en el reporte
     * mensual. Default 2000 (el valor que estaba hardcodeado).
     */
    public function up(): void
    {
        Schema::table('family_groups', function (Blueprint $table) {
            $table->decimal('ant_threshold', 15, 2)->default(2000)->after('invite_token');
        });
    }

    public function down(): void
    {
        Schema::table('family_groups', function (Blueprint $table) {
            $table->dropColumn('ant_threshold');
        });
    }
};
