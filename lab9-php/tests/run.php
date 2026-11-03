<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Student, Technician, Equipment, EquipmentCode, User};

function expectException(callable $action, string $expected, string $label): void
{
    try {
        $action();
        echo "FAIL: {$label} — исключение не выброшено\n";
    } catch (\Throwable $e) {
        if ($e instanceof $expected) {
            echo "OK: {$label} — {$e->getMessage()}\n";
        } else {
            echo "FAIL: {$label} — ожидалось {$expected}, получено " . get_class($e) . "\n";
        }
    }
}

function assertTrue(bool $cond, string $label): void
{
    echo ($cond ? 'OK: ' : 'FAIL: ') . $label . "\n";
}

// 1. Корректные объекты
$technician = new Technician(1, 'Анна Ким', 'a.kim@atu.edu.kz', 'Лаборатория ИТ');
$student    = new Student(10, 'Алия Нур', 'aliya@example.kz', 'ИС-22');
assertTrue(true, 'Созданы Technician и Student');

// 2. Пустое имя
expectException(
    fn() => new Student(2, '  ', 'x@example.kz', 'ИС-22'),
    \InvalidArgumentException::class,
    'Пустое имя'
);

// 3. Неверный email
expectException(
    fn() => new Student(3, 'Иван Иванов', 'bad-email', 'ИС-22'),
    \InvalidArgumentException::class,
    'Неверный email'
);

// 4. Неверный инвентарный номер
expectException(
    fn() => new EquipmentCode('abc'),
    \InvalidArgumentException::class,
    'Неверный формат инвентарного номера'
);

// 5. Успешная выдача
$eq = new Equipment(new EquipmentCode('EQ-0002'), 'Проектор', $technician);
$co = $eq->issue($student, new \DateTimeImmutable('+3 days'));
assertTrue($co->studentId === $student->id, 'Выдача создана, оборудование занято');

// 6. Повторная выдача тому же студенту — дубль
expectException(
    fn() => $eq->issue($student, new \DateTimeImmutable('+10 days')),
    \DomainException::class,
    'Повторная выдача (дубль)'
);

// 7. Выдача уже занятого оборудования другому студенту
$student2 = new Student(11, 'Данияр Серик', 'd@example.kz', 'ИС-22');
expectException(
    fn() => $eq->issue($student2, new \DateTimeImmutable('+5 days')),
    \DomainException::class,
    'Выдача занятого оборудования'
);

// 8. Неверный срок возврата (в прошлом)
$eq2 = new Equipment(new EquipmentCode('EQ-0003'), 'Планшет', $technician);
expectException(
    fn() => $eq2->issue($student2, new \DateTimeImmutable('-1 day')),
    \InvalidArgumentException::class,
    'Срок возврата в прошлом'
);

// 9. Возврат без активной выдачи
expectException(
    fn() => $eq->returnEquipment($student2),
    \DomainException::class,
    'Возврат несуществующей выдачи'
);

// 10. Полиморфизм — единый контракт
$card = static function (User $u): string { return $u->roleLabel(); };
assertTrue(
    $card($technician) !== $card($student),
    'Полиморфный вызов roleLabel() даёт разные результаты'
);

// 11. Успешный возврат
$eq->returnEquipment($student);
assertTrue($eq->isAvailable(), 'После возврата оборудование снова доступно');