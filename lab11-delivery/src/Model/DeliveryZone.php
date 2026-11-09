<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Model;

enum DeliveryZone: string
{
    case CITY = 'city';
    case REGION = 'region';
    case FEDERAL = 'federal';

    public function baseRate(): int
    {
        return match ($this) {
            self::CITY => 150,
            self::REGION => 350,
            self::FEDERAL => 700,
        };
    }

    public function maxWeightKg(): float
    {
        return match ($this) {
            self::CITY => 30.0,
            self::REGION => 50.0,
            self::FEDERAL => 20.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::CITY => 'Город',
            self::REGION => 'Область',
            self::FEDERAL => 'Межрегион',
        };
    }
}