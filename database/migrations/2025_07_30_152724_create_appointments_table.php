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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('client_name')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('service')->nullable();
            $table->timestamp('start_time');
            $table->integer('duration')->default(30); // in minutes
            $table->enum('status', ['Scheduled', 'Confirmed', 'Canceled'])->default('Scheduled');
            $table->timestamp('reminder_sent')->nullable();
            $table->text('notes')->nullable();
            $table->string('event_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
