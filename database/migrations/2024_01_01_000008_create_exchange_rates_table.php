<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * Tipo de cambio manual ingresado por el usuario.
         * family_group_id permite que cada grupo tenga su propio tipo de cambio.
         * Se usa el más reciente (por date) para conversiones en reportes unificados.
         */
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // quién lo cargó
            $table->string('from_currency', 3)->default('USD');
            $table->string('to_currency', 3)->default('ARS');
            $table->decimal('rate', 15, 4); // ej: 1 USD = 1050.5000 ARS
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['family_group_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
