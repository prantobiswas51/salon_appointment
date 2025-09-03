<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappWebhookController extends Controller
{
    /**
     * GET /webhook/whatsapp
     * Meta calls this once to verify your endpoint.
     * Must echo hub.challenge (plain text) with HTTP 200 if the token matches.
     */
    public function verify(Request $request)
    {
        // Meta sends ?hub.mode=subscribe&hub.challenge=...&hub.verify_token=...
        $mode      = $request->query('hub_mode');         // note underscore
        $token     = $request->query('hub_verify_token'); // note underscore
        $challenge = $request->query('hub_challenge');    // note underscore

        $expected  = 'mySecretTokenNN489'; // from .env

        if ($mode === 'subscribe' && $token === $expected) {
            return response($challenge, 200); // MUST be raw text
        }

        return response('Invalid verification token', 403);
    }

    /**
     * POST /webhook/whatsapp
     * WhatsApp sends messages, statuses, etc. here.
     */
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('WhatsApp Webhook (POST):', $data);

        // Safely read messages (may be absent for status-only updates)
        $messages = data_get($data, 'entry.0.changes.0.value.messages', []);

        foreach ($messages as $msg) {
            $from      = $msg['from'] ?? null;            // sender wa_id
            $type      = $msg['type'] ?? null;            // text, image, etc.
            $timestamp = $msg['timestamp'] ?? null;

            if ($type === 'text') {
                $text = data_get($msg, 'text.body');
                Log::info("Incoming text from {$from}: {$text}", ['timestamp' => $timestamp]);

                // TODO: store in DB, dispatch job, or trigger an auto-reply.
            }

            // You can add handlers for other types:
            // if ($type === 'image') { ... }
            // if ($type === 'interactive') { ... }
        }

        // (Optional) read status updates (sent/delivered/read)
        $statuses = data_get($data, 'entry.0.changes.0.value.statuses', []);
        foreach ($statuses as $st) {
            Log::info('Message status', $st);
            // e.g., track delivery/read states using $st['id'], $st['status'], $st['timestamp']
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
