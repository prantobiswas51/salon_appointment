<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapps', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->text('token')->nullable();
            $table->string('number_id')->nullable();
            $table->timestamps();
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapps');
    }
};
