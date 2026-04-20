<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->nullable()->constrained()->nullOnDelete(); // null = categoría global del sistema
            $table->string('name');
            $table->string('icon')->nullable();   // nombre de ícono (ej: heroicon slug)
            $table->string('color')->nullable();  // hex color para UI
            $table->enum('type', ['expense', 'income', 'both'])->default('both');
            $table->boolean('is_system')->default(false); // categorías predefinidas del sistema
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
