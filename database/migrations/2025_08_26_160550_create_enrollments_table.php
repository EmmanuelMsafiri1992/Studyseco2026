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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->text('address');
            $table->json('selected_subjects'); // Array of subject IDs
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method'); // tnm_mpamba, airtel_money, bank_transfer
            $table->string('payment_reference');
            $table->string('payment_proof_path')->nullable(); // File path for payment proof
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // If user creates account later
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
