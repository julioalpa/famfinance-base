<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tag_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 7)->default('#6a6676');
            $table->timestamps();
        });

        Schema::create('tag_group_tag', function (Blueprint $table) {
            $table->foreignId('tag_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['tag_group_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_group_tag');
        Schema::dropIfExists('tag_groups');
    }
};
