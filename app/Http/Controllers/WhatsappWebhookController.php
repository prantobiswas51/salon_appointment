<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode      = $request->query('hub_mode') ?? $request->query('hub.mode');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');
        $token     = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');

        if ($mode === 'subscribe' && $token === env('WHATSAPP_VERIFY_TOKEN')) {
            return response($challenge, 200);
        }
        return response('Forbidden', 403);
    }

    // POST /webhooks/whatsapp  (events)
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('WhatsApp webhook', $data);

        $messages = data_get($data, 'entry.0.changes.0.value.messages', []);
        if (!empty($messages)) {
            foreach ($messages as $msg) {
                if (($msg['type'] ?? null) === 'text') {
                    // enqueue a job to reply via WhatsappController::send or a service class
                }
            }
        }
        return response()->json(['status' => 'ok'], 200);
    }
}
