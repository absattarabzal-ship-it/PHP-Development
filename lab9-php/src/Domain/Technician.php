<?php
declare(strict_types=1);

namespace App\Domain;

final class Technician extends User
{
    public function __construct(
        int $id,
        string $fullName,
        string $email,
        public readonly string $department,
    ) {
        parent::__construct($id, $fullName, $email);
        if (trim($department) === '') {
            throw new \InvalidArgumentException('Отдел обязателен.');
        }
    }

    #[\Override]
    public function roleLabel(): string
    {
        return 'Техник ' . $this->department;
    }
}