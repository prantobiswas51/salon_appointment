<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    public function verify(Request $request)
    {
        // Meta sends ?hub.mode=subscribe&hub.challenge=...&hub.verify_token=...
        $challenge = $request->query('hub_challenge');     // note underscore
        $token     = $request->query('hub_verify_token');  // note underscore
        $mode      = $request->query('hub_mode');

        $expected = 'mySecretTokenNN489'; // see config/services.php

        if ($mode === 'subscribe' && $token === $expected) {
            return response($challenge, 200); // MUST return the raw challenge
        }

        return response('Invalid verification token', 403);
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
