<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Service;

use University\ParcelDelivery\Contract\TariffCalculatorInterface;
use University\ParcelDelivery\Model\DeliveryZone;
use University\ParcelDelivery\Model\Parcel;

final class WeightZoneTariffCalculator implements TariffCalculatorInterface
{
    private const EXTRA_PER_KG = 40;
    private const DISCOUNT_THRESHOLD_KG = 10.0;
    private const DISCOUNT_RATE = 0.10;

    public function calculate(Parcel $parcel, DeliveryZone $zone): int
    {
        $base = $zone->baseRate();
        $extraWeight = max(0.0, $parcel->weightKg - 5.0);
        $extraCost = (int) ceil($extraWeight * self::EXTRA_PER_KG);

        $courierFee = $zone === DeliveryZone::CITY ? 100 : 0;

        $expressFee = $parcel->express
            ? (int) round(($base + $extraCost + $courierFee) * 0.5)
            : 0;

        $subtotal = $base + $extraCost + $courierFee + $expressFee;

        if ($parcel->weightKg > self::DISCOUNT_THRESHOLD_KG) {
            $subtotal = (int) round($subtotal * (1 - self::DISCOUNT_RATE));
        }

        return $subtotal;
    }
}