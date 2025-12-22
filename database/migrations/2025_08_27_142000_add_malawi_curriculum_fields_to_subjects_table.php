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
        Schema::table('subjects', function (Blueprint $table) {
            $table->boolean('is_compulsory')->default(false)->after('is_active');
            $table->string('subject_type', 50)->nullable()->after('is_compulsory'); // core, science, humanities, technical, etc.
            $table->integer('sort_order')->default(0)->after('subject_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['is_compulsory', 'subject_type', 'sort_order']);
        });
    }
};