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
        Schema::create('whats_app_messages', function (Blueprint $table) {
            $table->id();
            $table->string('wa_message_id')->nullable()->unique(); // e.g. "wamid.HBgM..."
            $table->string('from_wa_id')->index();                 // sender wa_id (e.g., 8801xxxxxxx)
            $table->string('to_wa_id')->nullable()->index();       // your business wa_id (optional)
            $table->string('type')->index();                       // text, image, etc.
            $table->text('body')->nullable();                      // text body (if type=text)
            $table->enum('direction', ['inbound','outbound'])->default('inbound')->index();
            $table->string('status')->nullable()->index();         // received/sent/delivered/read/failed
            $table->timestamp('sent_at')->nullable()->index();     // convert from webhook timestamp
            $table->longText('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_messages');
    }
};
