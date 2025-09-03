<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $value = data_get($data, 'entry.0.changes.0.value', []);

        // Incoming messages (if any)
        $messages = $value['messages'] ?? [];
        if (!empty($messages)) {
            $msg       = $messages[0]; // just take the first for a quick check
            $type      = $msg['type'] ?? 'unknown';
            $from      = $msg['from'] ?? null;
            $waId      = $msg['id'] ?? null;
            $timestamp = isset($msg['timestamp']) ? (int)$msg['timestamp'] : null;

            $body = null;
            if ($type === 'text') {
                $body = data_get($msg, 'text.body');
            } elseif ($type === 'interactive') {
                $body = data_get($msg, 'interactive.button_reply.title')
                    ?? data_get($msg, 'interactive.list_reply.title');
            }

            // Save a compact snapshot to storage/app/wa-last.json
            $snapshot = [
                'wa_message_id' => $waId,
                'from_wa_id'    => $from,
                'type'          => $type,
                'body'          => $body,
                'timestamp'     => $timestamp,
                'sent_at_iso'   => $timestamp ? Carbon::createFromTimestamp($timestamp)->toIso8601String() : null,
            ];

            Storage::put('wa-last.json', json_encode($snapshot, JSON_PRETTY_PRINT));
            Log::info('Saved last WA message snapshot', $snapshot);
        }

        return response()->json(['status' => 'ok']);
    }
}
