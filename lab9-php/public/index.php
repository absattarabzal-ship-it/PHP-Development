<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Student, Technician, Equipment, EquipmentCode, User};

function printUserCard(User $user): void
{
    printf(
        "#%d %s — %s <%s>\n",
        $user->id,
        $user->fullName(),
        $user->roleLabel(),
        $user->email
    );
}

$technician = new Technician(1, 'Анна Ким', 'a.kim@atu.edu.kz', 'Лаборатория ИТ');
$student    = new Student(10, 'Алия Нур', 'aliya@example.kz', 'ИС-22');

$equipment  = new Equipment(
    new EquipmentCode('EQ-0001'),
    'Ноутбук Lenovo ThinkPad',
    $technician
);

$checkout = $equipment->issue(
    $student,
    new \DateTimeImmutable('+7 days')
);

foreach ([$technician, $student] as $user) {
    printUserCard($user);
}

echo "Выдано: {$equipment->title}, до {$checkout->dueDate->format('Y-m-d')}\n";
echo 'Доступно: ' . ($equipment->isAvailable() ? 'да' : 'нет') . PHP_EOL;