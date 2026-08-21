<?php

namespace App\Services\Delivery;

use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class YangoDeliveryPartner extends ManualDeliveryPartner
{
    public function requestDelivery(Order $order, array $data): void
    {
        if (! $this->isConfigured()) {
            parent::requestDelivery($order, $data);

            return;
        }

        $cfg = $this->config();

        try {
            $payload = $data;
            $payload['order_id'] = (int) $order->id;

            $res = Http::baseUrl($cfg['base_url'])
                ->withToken($cfg['token'])
                ->timeout((int) $cfg['timeout'])
                ->acceptJson()
                ->post($cfg['create_path'], $payload);

            $json = $res->json();

            $externalId = '';
            if (is_array($json)) {
                $externalId = (string) ($json['external_id'] ?? $json['delivery_id'] ?? $json['id'] ?? '');
            }

            $etaAt = null;
            if (is_array($json) && isset($json['eta_minutes']) && (int) $json['eta_minutes'] > 0) {
                $etaAt = Carbon::now()->addMinutes((int) $json['eta_minutes']);
            }

            $estimatedPrice = null;
            if (is_array($json) && array_key_exists('estimated_price', $json)) {
                $estimatedPrice = is_null($json['estimated_price']) ? null : (float) $json['estimated_price'];
            } elseif (is_array($json) && array_key_exists('price', $json)) {
                $estimatedPrice = is_null($json['price']) ? null : (float) $json['price'];
            }

            OrderDelivery::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'partner' => $this->cleanNullableString($data['partner'] ?? 'Yango') ?: 'Yango',
                    'external_id' => $this->cleanNullableString($externalId),
                    'estimated_price' => $estimatedPrice,
                    'currency' => $this->cleanNullableString($data['currency'] ?? null) ?: 'Kz',
                    'status' => 'requested',
                    'requested_at' => Carbon::now(),
                    'eta_at' => $etaAt,
                    'partner_status' => is_array($json) ? (string) ($json['status'] ?? $json['state'] ?? '') : '',
                    'raw_payload' => [
                        'mode' => 'yango',
                        'action' => 'request_delivery',
                        'request' => $payload,
                        'response_status' => $res->status(),
                        'response' => $json,
                    ],
                ]
            );
        } catch (\Throwable $e) {
            parent::requestDelivery($order, $data);
        }
    }

    public function cancelDelivery(Order $order, array $data = []): void
    {
        $order->loadMissing(['delivery']);
        $externalId = $this->cleanNullableString(optional($order->delivery)->external_id);

        if (! $this->isConfigured() || $externalId === '') {
            parent::cancelDelivery($order, $data);

            return;
        }

        $cfg = $this->config();
        if ((string) ($cfg['cancel_path'] ?? '') === '') {
            parent::cancelDelivery($order, $data);

            return;
        }

        try {
            $path = $this->renderPath((string) $cfg['cancel_path'], $externalId);
            $payload = $data;
            $payload['external_id'] = $externalId;

            $res = Http::baseUrl($cfg['base_url'])
                ->withToken($cfg['token'])
                ->timeout((int) $cfg['timeout'])
                ->acceptJson()
                ->post($path, $payload);

            $json = $res->json();

            OrderDelivery::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'status' => 'cancelled',
                    'partner_status' => is_array($json) ? (string) ($json['status'] ?? $json['state'] ?? 'cancelled') : 'cancelled',
                    'raw_payload' => [
                        'mode' => 'yango',
                        'action' => 'cancel_delivery',
                        'request' => $payload,
                        'response_status' => $res->status(),
                        'response' => $json,
                    ],
                ]
            );
        } catch (\Throwable $e) {
            parent::cancelDelivery($order, $data);
        }
    }

    public function fetchStatus(Order $order): array
    {
        $order->loadMissing(['delivery']);
        $externalId = $this->cleanNullableString(optional($order->delivery)->external_id);

        if (! $this->isConfigured() || $externalId === '') {
            return parent::fetchStatus($order);
        }

        $cfg = $this->config();
        if ((string) ($cfg['status_path'] ?? '') === '') {
            return parent::fetchStatus($order);
        }

        try {
            $path = $this->renderPath((string) $cfg['status_path'], $externalId);

            $res = Http::baseUrl($cfg['base_url'])
                ->withToken($cfg['token'])
                ->timeout((int) $cfg['timeout'])
                ->acceptJson()
                ->get($path);

            $json = $res->json();

            $updates = [
                'partner_status' => is_array($json) ? (string) ($json['status'] ?? $json['state'] ?? '') : null,
                'raw_payload' => [
                    'mode' => 'yango',
                    'action' => 'fetch_status',
                    'response_status' => $res->status(),
                    'response' => $json,
                ],
            ];

            if (is_array($json) && ! empty($json['driver_name'])) {
                $updates['driver_name'] = trim((string) $json['driver_name']);
            }
            if (is_array($json) && ! empty($json['driver_phone'])) {
                $updates['driver_phone'] = trim((string) $json['driver_phone']);
            }
            if (is_array($json) && isset($json['eta_minutes']) && (int) $json['eta_minutes'] > 0) {
                $updates['eta_at'] = Carbon::now()->addMinutes((int) $json['eta_minutes']);
            }

            OrderDelivery::query()->updateOrCreate(
                ['order_id' => $order->id],
                $updates
            );

            return parent::fetchStatus($order);
        } catch (\Throwable $e) {
            return parent::fetchStatus($order);
        }
    }

    private function isConfigured(): bool
    {
        $cfg = $this->config();

        return (string) ($cfg['base_url'] ?? '') !== ''
            && (string) ($cfg['token'] ?? '') !== ''
            && (string) ($cfg['create_path'] ?? '') !== '';
    }

    private function config(): array
    {
        $cfg = (array) config('services.yango', []);

        return [
            'base_url' => (string) ($cfg['base_url'] ?? ''),
            'token' => (string) ($cfg['token'] ?? ''),
            'create_path' => (string) ($cfg['create_path'] ?? ''),
            'cancel_path' => (string) ($cfg['cancel_path'] ?? ''),
            'status_path' => (string) ($cfg['status_path'] ?? ''),
            'timeout' => (int) ($cfg['timeout'] ?? 12),
        ];
    }

    private function renderPath(string $path, string $externalId): string
    {
        return str_replace(['{external_id}', '{delivery_id}', ':external_id', ':delivery_id'], $externalId, $path);
    }

    private function cleanNullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $v = trim((string) $value);

        return $v !== '' ? $v : null;
    }
}
