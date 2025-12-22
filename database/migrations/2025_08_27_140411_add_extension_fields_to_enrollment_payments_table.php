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
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->string('payment_type')->default('enrollment')->after('payment_details');
            $table->integer('extension_months')->nullable()->after('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'extension_months']);
        });
    }
};