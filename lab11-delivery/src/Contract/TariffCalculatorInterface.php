<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Contract;

use University\ParcelDelivery\Model\DeliveryZone;
use University\ParcelDelivery\Model\Parcel;

interface TariffCalculatorInterface
{
    public function calculate(Parcel $parcel, DeliveryZone $zone): int;
}