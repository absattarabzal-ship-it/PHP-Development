<?php
declare(strict_types=1);

namespace App\Domain;

final readonly class EquipmentCode
{
    public function __construct(public string $value)
    {
        if (preg_match('/^[A-Z]{2}-\d{4}$/', $value) !== 1) {
            throw new \InvalidArgumentException(
                'Формат инвентарного номера: две заглавные латинские буквы, дефис и 4 цифры (например, EQ-0001).'
            );
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}