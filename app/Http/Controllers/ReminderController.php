<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Reminder;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ReminderController extends Controller
{

    public function sendAppointmentReminders()
    {
        // Define the window for tomorrow (in local time, e.g., Asia/Dhaka)
        $tz = 'Asia/Dhaka';
        $tomorrowStart = Carbon::now($tz)->addDay()->startOfDay();
        $tomorrowEnd = Carbon::now($tz)->addDay()->endOfDay();

        $appointments = Appointment::with('client') // eager load client data
            ->whereBetween('start_time', [$tomorrowStart, $tomorrowEnd])
            ->where('reminder_sent', false)
            ->get();

        foreach ($appointments as $appointment) {
            $phone = $appointment->client->phone;
            $clientName = $appointment->client->name;
            $appointmentTime = Carbon::parse($appointment->start_time)->format('h:i A');

            // Prepare the message components (assuming you are using a template)
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $clientName],
                        ['type' => 'text', 'text' => $appointmentTime],
                    ],
                ],
            ];

            // Send the reminder message
            try {
                // WhatsApp API URL
                $url = sprintf(
                    'https://graph.facebook.com/%s/%s/messages','v22.0',751438628057967
                );

                // Payload for WhatsApp message
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to'       => '+8801823744169',
                    'type'     => 'template',
                    'template' => [
                        'name'     => 'Ki khobor?',
                        'language' => ['code' => config('services.whatsapp.tpl_lang')],
                        'components' => $components,
                    ],
                ];

                // Send the message via the WhatsApp Cloud API
                $response = Http::withToken('EAAKbuv0cwRcBPMU3gCIKKMODGoQKV2kyaf1GAlRfnAGIoAlVuK85KEbq3FzYaeDHcG7b04L4kvMstyzBeWiJUvf2tSBzkL7iSpCqGkq1XAkLlSZBHD4Tbw8ScHvPBhcm8i9dDjXHsMQy3M9FFlUuAjxaBSjE83ZAUPlyfW7Y2ofc2KgNGlpGZBJnVwWsyIg82q2MaJZBPbltvtJ0xoEuQwSRLBSVMyyWFviCXMZAgMaoZD')
                    ->asJson()
                    ->post($url, $payload);

                if ($response->failed()) {
                    throw new \Exception('WhatsApp send failed: ' . $response->status());
                }

                // Log the sent message in the reminder table
                Reminder::create([
                    'client_id' => $appointment->client_id,
                    'appointment_id' => $appointment->id,
                    'message_sent_at' => now(),
                ]);

                // Mark the appointment as having had the reminder sent
                $appointment->update(['reminder_sent' => true]);

                // Optionally, log the response or handle failure cases
                Log::info('Reminder sent', ['appointment_id' => $appointment->id, 'response' => $response->json()]);
            } catch (\Exception $e) {
                // Handle failure (e.g., log or notify about issues)
                Log::error('Failed to send reminder', ['appointment_id' => $appointment->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => 'Reminders sent successfully']);
    }

    
     public function index()
    {
        $reminders = Reminder::orderByDesc('created_at')->paginate(10);

        return Inertia::render('whatsapp/index', [
            'reminders' => $reminders,
        ]);
    }
}
