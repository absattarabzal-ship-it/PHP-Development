<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Model;

use University\ParcelDelivery\Exception\InvalidParcelException;

final class Courier
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly DeliveryZone $zone,
    ) {
        if (preg_match('/^C[0-9]{3}$/', $id) !== 1) {
            throw new InvalidParcelException(
                'Идентификатор курьера должен иметь вид C001.'
            );
        }

        if (trim($name) === '') {
            throw new InvalidParcelException('Имя курьера обязательно.');
        }
    }

    public function canServe(DeliveryZone $zone): bool
    {
        return $this->zone === $zone;
    }
}