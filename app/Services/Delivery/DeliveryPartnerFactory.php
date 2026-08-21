<?php

namespace App\Services\Delivery;

class DeliveryPartnerFactory
{
    public static function make(?string $partner): DeliveryPartnerInterface
    {
        $key = self::normalizeKey($partner);

        if ($key === 'yango') {
            return new YangoDeliveryPartner();
        }

        if (in_array($key, ['kubinga', 'tleva', 'ugo', 'heetch', 'bolt'], true)) {
            return new ManualDeliveryPartner();
        }

        return new ManualDeliveryPartner();
    }

    private static function normalizeKey(?string $partner): string
    {
        $value = mb_strtolower(trim((string) $partner));

        if ($value === '') {
            return '';
        }

        $value = str_replace(["\u{2019}", "\u{2018}", "\u{00B4}", '`'], "'", $value);
        $value = preg_replace("/\s+/", '', $value) ?? $value;
        $value = str_replace(['-', '_', '.'], '', $value);

        if ($value === "t'leva" || $value === 'tleva') {
            return 'tleva';
        }

        return $value;
    }
}
