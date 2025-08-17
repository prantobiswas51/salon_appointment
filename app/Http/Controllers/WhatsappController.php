<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'to'   => 'required|string',
            'body' => 'required|string|max:4096', // plain text
        ]);

        $phoneNumberId = '7514386280565757967';

        $url = "https://graph.facebook.com/v22.0/{$phoneNumberId}/messages";

        // If you want to send plain text (only works inside the 24h session window)
        // $payload = [
        //     'messaging_product' => 'whatsapp',
        //     'to'   => $validated['to'],
        //     'type' => 'text',
        //     'text' => [
        //         'body' => $validated['body'],
        //     ],
        // ];

        // If you need to send a template (outside the 24h window), uncomment below:

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'   => $validated['to'],
            'type' => 'template',
            'template' => [
                'name' => 'hello_world',
                'language' => ['code' => 'en_US'],
            ],
        ];

        $resp = Http::withToken('EAAKbuv0cwRcBPMzbKhohriugheriger40q3BPV8dMKaZBRaogOigdZAN8eRZC750RboGFI13KOhHgLRvlbW47eTCnmSj9zDNAPTz3C55f5XeAIwKrU326O8hwZAFUmznqBoWIRZCguI3JmAjZBIZCcN9ezm9blW6EOdZCUTJjMWTxrPHvz0giZBK2fl8kwZBpR4LkNDt6fdr4z4vUEZBVQR1PIvCLZChCQZDZD')
            ->acceptJson()
            ->post($url, $payload);

        if ($resp->failed()) {
            return response()->json([
                'error' => 'WhatsApp send failed',
                'meta'  => $resp->json(),
            ], $resp->status());
        }

        $reminder = new Reminder();
        $reminder->appointment_id = '1';
        $reminder->message_sent_at = Carbon::now();
        $reminder->save();

        return response()->json($resp->json());
    }
}
