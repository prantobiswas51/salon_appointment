<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendWhatsAppMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-whats-app-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Get all appointments scheduled for tomorrow
        $appointments = Appointment::whereDate('appointment_time', $tomorrow)
            ->where('status', 'Scheduled') // Ensure only scheduled appointments are considered
            ->get();

        foreach ($appointments as $appointment) {
            $client = $appointment->client;  // Get the associated client

            // Check if client's phone number exists
            if ($client->phone) {
                $to = $client->phone;
                $name = $client->name;
                $scheduled_time = Carbon::parse($appointment->appointment_time)->format('h:i A');

                // Send the WhatsApp message to the client
                $response = Http::post(url('/api/whatsapp/send-appointment'), [
                    'to' => $to,
                    'name' => $name,
                    'scheduled_time' => $scheduled_time
                ]);

                // Log success or failure
                if ($response->successful()) {
                    $this->info("WhatsApp message sent to {$name} at {$scheduled_time}");
                } else {
                    $this->error("Failed to send message to {$name}.");
                }
            }
        }
    }
}
