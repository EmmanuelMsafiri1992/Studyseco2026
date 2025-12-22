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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Student who made payment
            $table->enum('payment_method', ['tnm_mpamba', 'airtel_money', 'bank_transfer']); // Malawian payment methods
            $table->decimal('amount', 10, 2); // Payment amount in MWK (Malawi Kwacha)
            $table->string('reference_number')->nullable(); // Payment reference/transaction ID
            $table->string('proof_screenshot')->nullable(); // Screenshot file path
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Verification status
            $table->integer('access_duration_days')->default(30); // How many days of access this payment provides
            $table->date('access_expires_at')->nullable(); // When access expires (set after approval)
            $table->text('admin_notes')->nullable(); // Admin notes during verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who verified
            $table->timestamp('verified_at')->nullable(); // When payment was verified
            $table->text('rejection_reason')->nullable(); // Reason if rejected
            $table->timestamps();

            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
