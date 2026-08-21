<?php

namespace App\Services\Delivery;

use App\Models\Order;

interface DeliveryPartnerInterface
{
    public function requestDelivery(Order $order, array $data): void;

    public function startDelivery(Order $order, array $data): void;

    public function updateDetails(Order $order, array $data): void;

    public function cancelDelivery(Order $order, array $data = []): void;

    public function fetchStatus(Order $order): array;
}
