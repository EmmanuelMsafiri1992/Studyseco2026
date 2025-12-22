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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('assigned_tutor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('tutor_assigned_at')->nullable();
            $table->timestamp('last_extension_at')->nullable();
            $table->boolean('can_extend_before_expiry')->default(true);
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->nullable();
            $table->timestamp('refund_processed_at')->nullable();
            $table->text('refund_reference')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['assigned_tutor_id']);
            $table->dropColumn([
                'assigned_tutor_id',
                'tutor_assigned_at',
                'last_extension_at',
                'can_extend_before_expiry',
                'cancellation_reason',
                'cancelled_at',
                'refund_status',
                'refund_processed_at',
                'refund_reference'
            ]);
        });
    }
};
