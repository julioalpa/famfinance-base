<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // quién la creó / es dueño
            $table->string('name');
            $table->enum('type', ['cash', 'digital', 'credit']); // efectivo, digital, crédito
            $table->enum('currency', ['ARS', 'USD'])->default('ARS');

            // Solo para cuentas de crédito
            $table->unsignedTinyInteger('closing_day')->nullable(); // día de cierre (1-31)
            $table->unsignedTinyInteger('due_day')->nullable();   // día de vencimiento (1-31)
            $table->decimal('credit_limit', 15, 2)->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
