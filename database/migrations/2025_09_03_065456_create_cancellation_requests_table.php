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
        Schema::create('cancellation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->onDelete('set null');
            $table->string('cancellation_type')->default('enrollment'); // enrollment, payment, service
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('cancellation_deadline')->nullable(); // 14-day deadline
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->string('refund_status')->default('pending'); // pending, processed, completed, denied
            $table->timestamp('refund_processed_at')->nullable();
            $table->json('original_payment_data')->nullable(); // Store payment details for refund
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('cancellation_deadline');
            $table->index('requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cancellation_requests');
    }
};
