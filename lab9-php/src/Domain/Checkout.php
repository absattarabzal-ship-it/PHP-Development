<?php
declare(strict_types=1);

namespace App\Domain;

final readonly class Checkout
{
    public function __construct(
        public int $studentId,
        public EquipmentCode $equipmentCode,
        public \DateTimeImmutable $issuedAt,
        public \DateTimeImmutable $dueDate,
    ) {
        if ($studentId < 1) {
            throw new \InvalidArgumentException('Некорректный ID студента.');
        }
        if ($dueDate <= $issuedAt) {
            throw new \InvalidArgumentException('Срок возврата должен быть позже даты выдачи.');
        }
    }

    public function isOverdue(\DateTimeImmutable $now): bool
    {
        return $now > $this->dueDate;
    }
}