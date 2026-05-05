<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 7)->default('#6a6676');
            $table->timestamps();

            $table->index('family_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
