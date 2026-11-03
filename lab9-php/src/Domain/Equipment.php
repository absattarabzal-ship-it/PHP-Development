<?php
declare(strict_types=1);

namespace App\Domain;

final class Equipment
{
    /** @var list<Checkout> */
    private array $checkouts = [];

    public function __construct(
        public readonly EquipmentCode $code,
        public readonly string $title,
        public readonly Technician $responsible,
    ) {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Название оборудования обязательно.');
        }
    }

    public function issue(Student $student, \DateTimeImmutable $dueDate, ?\DateTimeImmutable $now = null): Checkout
    {
        if (!$this->isAvailable()) {
            throw new \DomainException('Оборудование уже выдано.');
        }
        $now ??= new \DateTimeImmutable();
        if ($dueDate <= $now) {
            throw new \InvalidArgumentException('Срок возврата должен быть в будущем.');
        }

        foreach ($this->checkouts as $c) {
            if ($c->studentId === $student->id) {
                throw new \DomainException('Студент уже держит это оборудование.');
            }
        }
        if ($this->activeCountFor($student->id) >= $student->maxActiveCheckouts()) {
            throw new \DomainException('Превышен лимит одновременных выдач для студента.');
        }

        $checkout = new Checkout($student->id, $this->code, $now, $dueDate);
        $this->checkouts[] = $checkout;
        return $checkout;
    }

    public function returnEquipment(Student $student): void
    {
        foreach ($this->checkouts as $i => $c) {
            if ($c->studentId === $student->id) {
                unset($this->checkouts[$i]);
                $this->checkouts = array_values($this->checkouts);
                return;
            }
        }
        throw new \DomainException('Нет активной выдачи для этого студента.');
    }

    public function isAvailable(): bool
    {
        return $this->checkouts === [];
    }

    public function activeCountFor(int $studentId): int
    {
        $n = 0;
        foreach ($this->checkouts as $c) {
            if ($c->studentId === $studentId) {
                $n++;
            }
        }
        return $n;
    }

    /** @return list<Checkout> */
    public function checkouts(): array
    {
        return $this->checkouts;
    }
}