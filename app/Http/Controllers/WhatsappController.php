<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function send()
    {

        $phoneNumberId = '821517547705035';

        $url = "https://graph.facebook.com/v23.0/{$phoneNumberId}/messages";

        // If you want to send plain text (only works inside the 24h session window)

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'   => '+8801823744169',
            'type' => 'text',
            'text' => [
                'body' => 'Hi , this is custom text',
            ],
        ];


        // If you need to send a template (outside the 24h window), uncomment below:

        // $payload = [
        //     'messaging_product' => 'whatsapp',
        //     'to'   => '01402036221',
        //     'type' => 'template',
        //     'template' => [
        //         'name' => 'hello_world',
        //         'language' => ['code' => 'en_US'],
        //     ],
        // ];

        $resp = Http::withToken('EAAYRK1176M0BPbO7ueHvSA3DuR520L1aQydl1Pyx2iKbDiDzYGY2kpyEqhZAB3sT784EeUSCpXRxZCrbFhu6emxUeHQFWic3bcsgjBhPjUZBPOdfBoGIqkTslQsi2QJBG1SHIBbJNLDXZC64KqSuPlzAXqfieuN7CobVmkBzsu8doIpLhtRvyBEZB77S6dA8CH7BOZC5pDqGt0xHGX')
            ->acceptJson()
            ->post($url, $payload);

        if ($resp->failed()) {
            return response()->json([
                'error' => 'WhatsApp send failed',
                'meta'  => $resp->json(),
            ], $resp->status());
        }


        return response()->json($resp->json());
    }

    public function index()
    {
        return Inertia::render('whatsapp/index');
    }
}
