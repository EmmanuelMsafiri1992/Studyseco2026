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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Display name (e.g., "TNM Mpamba")
            $table->string('key')->unique(); // Internal key (e.g., "tnm_mpamba")
            $table->string('type'); // mobile_money, bank_transfer, etc.
            $table->text('instructions')->nullable(); // Payment instructions
            $table->json('config')->nullable(); // Phone numbers, account details, etc.
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
