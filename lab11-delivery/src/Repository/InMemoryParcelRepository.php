<?php

declare(strict_types=1);

namespace University\ParcelDelivery\Repository;

use University\ParcelDelivery\Contract\ParcelRepositoryInterface;
use University\ParcelDelivery\Model\Parcel;

final class InMemoryParcelRepository implements ParcelRepositoryInterface
{
    /** @var array<string, Parcel> */
    private array $items = [];

    public function save(Parcel $parcel): void
    {
        $this->items[$parcel->tracking] = $parcel;
    }

    public function findByTracking(string $tracking): ?Parcel
    {
        return $this->items[$tracking] ?? null;
    }

    public function all(): array
    {
        return array_values($this->items);
    }
}