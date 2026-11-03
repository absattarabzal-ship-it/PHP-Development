<?php
declare(strict_types=1);

namespace App\Domain;

final class Student extends User
{
    public function __construct(
        int $id,
        string $fullName,
        string $email,
        public readonly string $group,
        private int $maxActiveCheckouts = 3,
    ) {
        parent::__construct($id, $fullName, $email);
        if (trim($group) === '') {
            throw new \InvalidArgumentException('Группа обязательна.');
        }
        if ($maxActiveCheckouts < 1) {
            throw new \InvalidArgumentException('Лимит выдач должен быть положительным.');
        }
    }

    #[\Override]
    public function roleLabel(): string
    {
        return 'Студент ' . $this->group;
    }

    public function maxActiveCheckouts(): int
    {
        return $this->maxActiveCheckouts;
    }
}