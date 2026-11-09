<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Contract;

use University\ParcelDelivery\Model\Parcel;

interface ParcelRepositoryInterface
{
    public function save(Parcel $parcel): void;

    public function findByTracking(string $tracking): ?Parcel;

    /** @return list<Parcel> */
    public function all(): array;
}