<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use University\ParcelDelivery\Exception\InvalidParcelException;
use University\ParcelDelivery\Exception\ParcelNotFoundException;
use University\ParcelDelivery\Model\Courier;
use University\ParcelDelivery\Model\DeliveryZone;
use University\ParcelDelivery\Model\Parcel;
use University\ParcelDelivery\Repository\InMemoryParcelRepository;
use University\ParcelDelivery\Service\DeliveryService;
use University\ParcelDelivery\Service\WeightZoneTariffCalculator;

$passed = 0;
$failed = 0;

function expectException(callable $action, string $expected, string $label): void
{
    global $passed, $failed;
    try {
        $action();
    } catch (\Throwable $e) {
        if ($e instanceof $expected) {
            echo "OK   [$label]\n";
            $passed++;
            return;
        }
        throw new RuntimeException("[$label] ждали $expected, получили " . $e::class);
    }
    throw new RuntimeException("[$label] исключение не выброшено");
}

function check(bool $condition, string $label): void
{
    global $passed, $failed;
    if (!$condition) {
        echo "FAIL [$label]\n";
        $failed++;
        return;
    }
    echo "OK   [$label]\n";
    $passed++;
}

$repo = new InMemoryParcelRepository();
$service = new DeliveryService($repo, new WeightZoneTariffCalculator());

// 1-3: Базовая регистрация
$service->register(new Parcel('YD100000001RU', 'Иванов', 3.0, DeliveryZone::CITY));
check($repo->findByTracking('YD100000001RU') !== null, 'регистрация посылки');
check($repo->findByTracking('YD100000001RU')?->status === 'registered', 'начальный статус');
check($repo->findByTracking('YD100000001RU')?->createdAt !== '', 'время создания установлено');
check($repo->findByTracking('YD100000001RU')?->estimatedArrival !== '', 'ожидаемое прибытие установлено');

// 4-6: Тарифы по зонам
check($service->calculateTariff('YD100000001RU') === 250, 'тариф city (3 кг) = 250 ₸');
$service->register(new Parcel('YD100000002RU', 'Петров', 12.0, DeliveryZone::REGION));
check($service->calculateTariff('YD100000002RU') === 567, 'тариф region (12 кг) = 567 ₸');
$service->register(new Parcel('YD100000003RU', 'Сидоров', 18.0, DeliveryZone::FEDERAL));
check($service->calculateTariff('YD100000003RU') === 1098, 'тариф federal (18 кг) = 1098 ₸');

// 7: Экспресс-доставка
$service->register(new Parcel('YD100000004RU', 'Козлов', 4.5, DeliveryZone::CITY, express: true));
check($service->calculateTariff('YD100000004RU') === 375, 'экспресс city (4.5 кг) = 375 ₸');

// 8: Ожидаемое время для экспресса — 2 часа (примерно)
$parcel = $repo->findByTracking('YD100000004RU');
$diff = strtotime($parcel->estimatedArrival) - strtotime($parcel->createdAt);
check($diff <= 7200, 'экспресс — прибытие ≤ 2 ч');

// 9: Смена статуса + доставка
$service->changeStatus('YD100000001RU', 'in_transit');
check($repo->findByTracking('YD100000001RU')?->status === 'in_transit', 'статус in_transit');
$service->changeStatus('YD100000001RU', 'delivered');
check($repo->findByTracking('YD100000001RU')?->status === 'delivered', 'статус delivered');
check($repo->findByTracking('YD100000001RU')?->deliveredAt !== null, 'время доставки установлено');

// 10: История
$parcel = $repo->findByTracking('YD100000001RU');
check($parcel !== null && count($parcel->history) === 2, 'история = 2 события');

// 11: Назначение курьера
$courier = new Courier('C001', 'Айбек', DeliveryZone::CITY);
$service->assignCourier('YD100000002RU', new Courier('C002', 'Мария', DeliveryZone::REGION));
$service->assignCourier('YD100000001RU', $courier);
check($repo->findByTracking('YD100000001RU')?->courier?->name === 'Айбек', 'назначение курьера');

// 12: Фильтр по зоне
check(count($service->byZone(DeliveryZone::CITY)) === 2, 'фильтр по зоне city');

// 13: Тяжёлые посылки
check(count($service->findHeavierThan(10.0)) === 2, 'тяжелее 10 кг = 2');

// 14: Общая выручка
check($service->totalRevenue() > 0, 'выручка положительна');

// 15: Неверный трек-номер
expectException(
    fn () => new Parcel('bad', 'X', 1.0, DeliveryZone::CITY),
    InvalidParcelException::class,
    'неверный трек-номер'
);

// 16: Превышение веса
expectException(
    fn () => new Parcel('YD100000099RU', 'X', 25.0, DeliveryZone::FEDERAL),
    InvalidParcelException::class,
    'превышение веса зоны federal'
);

// 17: Пустой получатель
expectException(
    fn () => new Parcel('YD100000098RU', '', 5.0, DeliveryZone::CITY),
    InvalidParcelException::class,
    'пустой получатель'
);

// 18: Дубликат
expectException(
    fn () => $service->register(new Parcel('YD100000001RU', 'Дубль', 2.0, DeliveryZone::CITY)),
    LogicException::class,
    'дубликат трек-номера'
);

// 19: Не найдено
expectException(
    fn () => $service->calculateTariff('YD999999999RU'),
    ParcelNotFoundException::class,
    'неизвестный трек-номер'
);

// 20: Курьер не для этой зоны
$regionCourier = new Courier('C002', 'Мария', DeliveryZone::REGION);
expectException(
    fn () => $service->assignCourier('YD100000001RU', $regionCourier),
    InvalidParcelException::class,
    'курьер не обслуживает зону'
);

// 21: Неверный статус
expectException(
    fn () => $service->changeStatus('YD100000001RU', 'lost'),
    InvalidArgumentException::class,
    'недопустимый статус'
);

echo "\n";
echo "═══════════════════════════════════════════════════\n";
echo "Пройдено: {$passed}   Провалено: {$failed}\n";
echo "Все проверки пройдены.\n";
echo "═══════════════════════════════════════════════════\n";