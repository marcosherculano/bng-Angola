<?php

namespace App\Services\Delivery;

use App\Models\Order;
use App\Models\OrderDelivery;
use Illuminate\Support\Carbon;

class ManualDeliveryPartner implements DeliveryPartnerInterface
{
    public function requestDelivery(Order $order, array $data): void
    {
        OrderDelivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'partner' => $this->cleanNullableString($data['partner'] ?? null),
                'external_id' => $this->cleanNullableString($data['external_id'] ?? null),
                'driver_name' => $this->cleanNullableString($data['driver_name'] ?? null),
                'driver_phone' => $this->cleanNullableString($data['driver_phone'] ?? null),
                'estimated_price' => $data['estimated_price'] ?? null,
                'currency' => $this->cleanNullableString($data['currency'] ?? 'Kz') ?: 'Kz',
                'notes' => $this->cleanNullableString($data['notes'] ?? null),
                'status' => 'requested',
                'requested_at' => Carbon::now(),
                'raw_payload' => [
                    'mode' => 'manual',
                    'action' => 'request_delivery',
                    'data' => $data,
                ],
            ]
        );
    }

    public function startDelivery(Order $order, array $data): void
    {
        OrderDelivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'external_id' => $this->cleanNullableString($data['external_id'] ?? null),
                'driver_name' => $this->cleanNullableString($data['driver_name'] ?? null),
                'driver_phone' => $this->cleanNullableString($data['driver_phone'] ?? null),
                'status' => 'in_progress',
                'started_at' => Carbon::now(),
                'raw_payload' => [
                    'mode' => 'manual',
                    'action' => 'start_delivery',
                    'data' => $data,
                ],
            ]
        );
    }

    public function updateDetails(Order $order, array $data): void
    {
        $updates = [];

        foreach (['partner', 'external_id', 'driver_name', 'driver_phone', 'currency', 'notes'] as $key) {
            if (array_key_exists($key, $data)) {
                $updates[$key] = $this->cleanNullableString($data[$key]);
            }
        }

        foreach (['status', 'requested_at', 'started_at', 'delivered_at'] as $key) {
            if (array_key_exists($key, $data)) {
                $updates[$key] = $data[$key];
            }
        }

        if (array_key_exists('estimated_price', $data)) {
            $updates['estimated_price'] = $data['estimated_price'];
        }

        if (array_key_exists('currency', $data)) {
            $updates['currency'] = $this->cleanNullableString($data['currency'] ?? 'Kz') ?: 'Kz';
        }

        if (empty($updates)) {
            return;
        }

        $updates['raw_payload'] = [
            'mode' => 'manual',
            'action' => 'update_details',
            'data' => $data,
        ];

        OrderDelivery::query()->updateOrCreate(
            ['order_id' => $order->id],
            $updates
        );
    }

    public function cancelDelivery(Order $order, array $data = []): void
    {
        $this->updateDetails($order, [
            'status' => 'cancelled',
            'notes' => $this->cleanNullableString($data['cancel_reason'] ?? ($data['notes'] ?? null)),
        ]);
    }

    public function fetchStatus(Order $order): array
    {
        $order->loadMissing(['delivery']);

        $delivery = $order->delivery;
        if (! $delivery) {
            return [];
        }

        return [
            'partner_status' => (string) ($delivery->partner_status ?? ''),
            'status' => (string) ($delivery->status ?? ''),
            'eta_at' => optional($delivery->eta_at)->toISOString(),
            'driver_name' => (string) ($delivery->driver_name ?? ''),
            'driver_phone' => (string) ($delivery->driver_phone ?? ''),
            'external_id' => (string) ($delivery->external_id ?? ''),
        ];
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
