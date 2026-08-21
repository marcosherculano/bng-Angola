<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class YangoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = (string) env('YANGO_WEBHOOK_SECRET', '');
        $headerName = (string) env('YANGO_WEBHOOK_SECRET_HEADER', 'X-Webhook-Secret');

        if ($secret !== '') {
            $incoming = (string) $request->headers->get($headerName, '');
            if (! hash_equals($secret, $incoming)) {
                return response()->json([
                    'message' => 'Assinatura inválida.',
                ], Response::HTTP_UNAUTHORIZED);
            }
        }

        $payload = $request->all();
        $event = $payload['event'] ?? ($payload['type'] ?? null);
        $externalId = $payload['id'] ?? ($payload['delivery_id'] ?? ($payload['order_id'] ?? null));

        Log::info('Yango webhook recebido (stub).', [
            'event' => $event,
            'external_id' => $externalId,
            'payload_keys' => is_array($payload) ? array_slice(array_keys($payload), 0, 30) : [],
        ]);

        return response()->json([
            'ok' => true,
        ]);
    }
}
