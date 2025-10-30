<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhatsappWebhookController extends Controller
{
   
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

  
    public function handle(Request $request)
    {
        $data = $request->all();
        Log::info('WhatsApp Webhook (POST):', $data);

        // WhatsApp may batch multiple entries/changes
        $entries = $data['entry'] ?? [];
        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];
            foreach ($changes as $change) {
                $value = $change['value'] ?? [];

                // 3a) Incoming messages (users -> you)
                foreach (($value['messages'] ?? []) as $msg) {
                    $type      = $msg['type'] ?? null;             // text, image, etc.
                    $from      = $msg['from'] ?? null;             // sender wa_id
                    $to        = $value['metadata']['display_phone_number'] ?? null; // optional
                    $waId      = $msg['id'] ?? null;               // "wamid..."
                    $timestamp = isset($msg['timestamp']) ? (int)$msg['timestamp'] : null;
                    $sentAt    = $timestamp ? \Carbon\Carbon::createFromTimestamp($timestamp) : null;

                    $body = null;
                    if ($type === 'text') {
                        $body = data_get($msg, 'text.body');
                    } elseif ($type === 'interactive') {
                        // example: button reply title
                        $body = data_get($msg, 'interactive.button_reply.title')
                            ?? data_get($msg, 'interactive.list_reply.title');
                    }

                    WhatsAppMessage::updateOrCreate(
                        ['wa_message_id' => $waId],
                        [
                            'from_wa_id'  => $from,
                            'to_wa_id'    => $to,
                            'type'        => $type ?? 'unknown',
                            'body'        => $body,
                            'direction'   => 'inbound',
                            'status'      => 'received',
                            'sent_at'     => $sentAt,
                            'raw_payload' => json_encode($msg),
                        ]
                    );
                }

                // 3b) Status updates (for messages you sent via API)
                foreach (($value['statuses'] ?? []) as $st) {
                    // st: id (wamid...), status (sent|delivered|read|failed), timestamp, recipient_id
                    $wamid   = $st['id'] ?? null;
                    $status  = $st['status'] ?? null;
                    $ts      = isset($st['timestamp']) ? (int)$st['timestamp'] : null;
                    $sentAt  = $ts ? \Carbon\Carbon::createFromTimestamp($ts) : null;

                    if ($wamid) {
                        WhatsAppMessage::where('wa_message_id', $wamid)->update([
                            'status'  => $status,
                            'sent_at' => $sentAt, // keep latest known
                        ]);
                    } else {
                        Log::warning('Status without wamid', $st);
                    }
                }
            }
        }
    }
}
