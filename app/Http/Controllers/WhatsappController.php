<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Reminder;
use App\Models\Whatsapp;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function index()
    {
        $whatsapps = Whatsapp::all();

        return Inertia::render('whatsapp/index', [
            'whatsapps' => $whatsapps,
        ]);
    }

    public function update(Whatsapp $whatsapp)
    {
        request()->validate([
            'message' => 'required|string',
            'token' => 'required|string',
            'number_id' => 'required|string',
        ]);

        $whatsapp->update(request()->only('message', 'token', 'number_id'));

        return redirect()->back()->with('success', 'Whatsapp updated!');
    }

    public function manual_message(Request $request)
    {
        try {
            // Find appointment with client relation
            $booking = Appointment::with('client')->findOrFail($request->id);

            if (!$booking->client) {
                Log::warning('WhatsApp send failed - no client found', [
                    'appointment_id' => $booking->id,
                ]);
                return false;
            }

            $client        = $booking->client;
            $client_number = $client->phone;
            $client_name   = $client->name;

            // Format appointment time
            $appointment_time = $booking->start_time
                ? Carbon::parse($booking->start_time)->format('d M Y, h:i A')
                : '0:00';

            // Get WhatsApp config
            $whatsapp = Whatsapp::firstOrFail();
            $token    = $whatsapp->token;
            $phoneId  = $whatsapp->number_id;

            $url = "https://graph.facebook.com/v22.0/{$phoneId}/messages";

            // WhatsApp Template Payload
            $payload = [
                'messaging_product' => 'whatsapp',
                'to'   => $client_number,
                'type' => 'template',
                'template' => [
                    'name'     => 'appointment_reminder', // template name in WABA
                    'language' => ['code' => 'it'],     // Italian template
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $client_name],
                                ['type' => 'text', 'text' => $appointment_time],
                            ],
                        ],
                    ],
                ],
            ];

            $resp = Http::withToken($token)
                ->acceptJson()
                ->withoutVerifying()
                ->post($url, $payload);

            if ($resp->successful()) {
                Log::info('WhatsApp template message sent', [
                    'appointment_id' => $booking->id,
                    'to'             => $client_number,
                    'client'         => $client_name,
                    'template'       => 'appointment_reminder',
                ]);
                return redirect()->back()->with('success', 'Reminder Sent!');
            }

            // Log failure details
            Log::warning('WhatsApp send failed', [
                'appointment_id' => $booking->id,
                'status'         => $resp->status(),
                'response'       => $resp->json(),
                'to'             => $client_number,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp send exception', [
                'error'          => $e->getMessage(),
                'appointment_id' => $request->id,
            ]);
            return false;
        }
    }
}
