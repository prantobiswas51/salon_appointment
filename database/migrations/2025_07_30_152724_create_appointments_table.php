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
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('service')->nullable();
            $table->timestamp('start_time');
            $table->integer('duration')->default(30); // in minutes
            $table->enum('attendance_status', ['attended', 'canceled', 'no_show', 'pending'])->nullable();
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
