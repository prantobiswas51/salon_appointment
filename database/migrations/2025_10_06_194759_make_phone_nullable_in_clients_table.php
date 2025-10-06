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
        Schema::table('clients', function (Blueprint $table) {
            // First drop the existing unique constraint
            $table->dropUnique(['phone']);
        });
        
        Schema::table('clients', function (Blueprint $table) {
            // Make phone nullable and add unique constraint back
            $table->string('phone')->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone')->unique()->change();
        });
    }
};
