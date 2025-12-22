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
        Schema::table('payment_methods', function (Blueprint $table) {
            // Add new columns
            $table->string('region')->after('type')->nullable();
            $table->string('currency', 3)->after('region')->nullable();
            
            // Rename columns
            $table->renameColumn('key', 'code');
            $table->renameColumn('config', 'requirements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            // Reverse column renames
            $table->renameColumn('code', 'key');
            $table->renameColumn('requirements', 'config');
            
            // Drop new columns
            $table->dropColumn(['region', 'currency']);
        });
    }
};
