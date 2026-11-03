<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Domain\{Student, Technician, Equipment, EquipmentCode, User};

function ask(string $prompt): string {
    echo $prompt;
    $input = fgets(STDIN);
    return trim($input);
}

echo "=== Система учета оборудования ===\n";

// 1. Создаем техника
echo "\nШаг 1. Создайте техника.\n";
$techName = ask("Введите имя техника: ");
$techDept = ask("Введите его отдел: ");
$technician = new Technician(1, $techName, 'tech@example.com', $techDept);

// 2. Создаем оборудование
echo "\nШаг 2. Создайте оборудование.\n";
$eqCode = ask("Введите инвентарный номер (формат: EQ-1234): ");
$eqTitle = ask("Введите название оборудования: ");
$equipment = new Equipment(new EquipmentCode($eqCode), $eqTitle, $technician);

// 3. Создаем студента
echo "\nШаг 3. Создайте студента.\n";
$studName = ask("Введите имя студента: ");
$studGroup = ask("Введите группу: ");
$student = new Student(10, $studName, 'student@example.com', $studGroup);

// 4. Выдаем оборудование
echo "\nШаг 4. Выдача оборудования.\n";
echo "Сколько дней выдается оборудование? ";
$days = (int)ask("Введите число дней: ");

try {
    $checkout = $equipment->issue($student, new \DateTimeImmutable("+{$days} days"));
    echo "\n--- УСПЕХ ---\n";
    echo "Оборудование: {$equipment->title}\n";
    echo "Выдано студенту: {$student->fullName()} (Группа: {$student->group})\n";
    echo "Дата возврата: {$checkout->dueDate->format('Y-m-d')}\n";
} catch (\Exception $e) {
    echo "\n--- ОШИБКА ---\n";
    echo $e->getMessage() . "\n";
}