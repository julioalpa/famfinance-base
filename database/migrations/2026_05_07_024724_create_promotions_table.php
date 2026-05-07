<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('provider')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed_amount'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->enum('currency', ['ARS', 'USD'])->default('ARS');
            $table->decimal('original_amount', 15, 2)->nullable();
            $table->date('starts_at')->nullable();
            $table->date('expires_at');
            $table->unsignedSmallInteger('reminder_days_before')->default(30);
            $table->timestamp('alerted_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['family_group_id', 'expires_at']);
            $table->index(['is_active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
