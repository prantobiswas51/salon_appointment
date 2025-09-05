<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Appointment;
use App\Models\Whatsapp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SendWhatsAppMessage extends Command
{

    protected $signature = 'appointments:notify-tomorrow {--dry-run}';
    protected $description = 'Send WhatsApp reminders for tomorrow\'s scheduled appointments';

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        // Window for *tomorrow* in app timezone
        $start = now()->tomorrow()->startOfDay();
        $end   = now()->tomorrow()->endOfDay();

        $this->info("Scanning appointments from {$start} to {$end}" . ($dryRun ? ' [DRY RUN]' : ''));

        Appointment::with('client')
            ->whereBetween('appointment_time', [$start, $end])
            ->where('status', 'Scheduled')
            ->orderBy('id')
            ->chunkById(200, function ($appointments) use ($dryRun) {
                foreach ($appointments as $appointment) {
                    $client = $appointment->client;
                    if (!$client || empty($client->phone)) {
                        continue;
                    }

                    $to   = trim($client->phone);
                    $name = $client->name ?: 'Customer';
                    $time = optional($appointment->appointment_time)
                        ? $appointment->appointment_time->timezone('Asia/Dhaka')->format('h:i A')
                        : null;

                    // Compose your message (customize as you like)
                    $message = "Hi {$name}, this is a reminder that your appointment is scheduled for tomorrow at {$time}. Reply if you need to reschedule.";

                    if ($dryRun) {
                        $this->line("DRY RUN → Would send to {$to}: \"{$message}\"");
                        continue;
                    }

                    try {
                        $ok = $this->sendWhatsApp($to, $message);

                        if ($ok) {
                            $this->info("WhatsApp message sent to {$name} ({$to}) for {$time}");
                        } else {
                            $this->error("Failed to send message to {$name} ({$to})");
                        }
                    } catch (\Throwable $e) {
                        Log::error('WhatsApp send failed', [
                            'appointment_id' => $appointment->id,
                            'phone' => $to,
                            'error' => $e->getMessage(),
                        ]);
                        $this->error("Exception sending message to {$name} ({$to})");
                    }
                }
            });

        return self::SUCCESS;
    }

    private function sendWhatsApp(string $to, string $message): bool
    {

        $whatsapp = Whatsapp::first();

        $token         = $whatsapp->token;
        $phoneNumberId = $whatsapp->number_id;
        
        $url = "https://graph.facebook.com/v23.0/{$phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'   => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        try {
            $resp = Http::withToken($token)
                ->acceptJson()
                ->withoutVerifying()
                ->post($url, $payload);

            if ($resp->successful()) {
                return true;
            }

            // Log *everything* Meta returns so we can diagnose quickly
            $json = $resp->json();
            Log::warning('WhatsApp send failed', [
                'status' => $resp->status(),
                'body'   => $json,
                'to'     => $to,
            ]);

            // If you’re calling this from a console command, you can echo a short reason:
            if (method_exists($this, 'error')) {
                $reason = $json['error']['message'] ?? 'Unknown error';
                $code   = $json['error']['code']    ?? 'n/a';
                $this->error("WhatsApp send failed ({$code}): {$reason}");
            }

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception', [
                'error' => $e->getMessage(),
                'to'    => $to,
            ]);
            if (method_exists($this, 'error')) {
                $this->error("WhatsApp send exception: {$e->getMessage()}");
            }
            return false;
        }
    }
}
