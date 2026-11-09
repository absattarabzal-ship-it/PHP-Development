<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use University\ParcelDelivery\Model\Courier;
use University\ParcelDelivery\Model\DeliveryZone;
use University\ParcelDelivery\Model\Parcel;
use University\ParcelDelivery\Repository\InMemoryParcelRepository;
use University\ParcelDelivery\Service\DeliveryService;
use University\ParcelDelivery\Service\WeightZoneTariffCalculator;

$repository = new InMemoryParcelRepository();
$service = new DeliveryService($repository, new WeightZoneTariffCalculator());

$senders = ['Iman BBQ Doner&Burger', 'Okadzaki', 'Coffee Boom', 'KFC', 'Holiday', 'Burger King', 'Dodo Pizza'];
$recipients = [
    'Данияр Ахметов', 'Айгерим Сериковна', 'Ерасыл Кайратович', 'Мадина Бекова',
    'Алишер Нурланов', 'Жанна Ахметова', 'Санжар Оспанов', 'Алия Жумабаева'
];
$addresses = [
    'ул. Абая, 15', 'пр. Достык, 105', 'ул. Сатпаева, 22/1',
    'мкр. Самал-2, 33', 'ул. Желтоксан, 8', 'пр. Аль-Фараби, 77'
];
$productsList = [
    'Донер', 'Суши-сет', 'Капучино', 'Чизбургер',
    'Картофель фри', 'Пицца', 'Кола', 'Латте'
];

function generateRandomOrder(DeliveryService $service, array $recipients, array $addresses, array $productsList): void
{
    $tracking = 'YD' . rand(100000000, 999999999) . 'RU';
    $recipient = $recipients[array_rand($recipients)];
    $address = $addresses[array_rand($addresses)];

    $items = [];
    $itemCount = rand(1, 3);
    for ($i = 0; $i < $itemCount; $i++) {
        $items[$productsList[array_rand($productsList)]] = rand(1, 2);
    }

    $weight = round(rand(10, 250) / 10, 1);
    $zone = DeliveryZone::CITY;
    $rand = rand(1, 100);
    if ($rand > 70 && $rand <= 90) {
        $zone = DeliveryZone::REGION;
    } elseif ($rand > 90) {
        $zone = DeliveryZone::FEDERAL;
    }

    $express = rand(1, 100) > 80;

    try {
        $hoursAgo = rand(1, 3);
        $createdAt = date('Y-m-d H:i:s', strtotime("-{$hoursAgo} hours"));
        $arrivalHours = $express ? 2 : 24;
        $estimatedArrival = date('Y-m-d H:i:s', strtotime("{$createdAt} +{$arrivalHours} hours"));

        $parcel = new Parcel(
            tracking: $tracking,
            recipient: $recipient,
            address: $address,
            items: $items,
            weightKg: $weight,
            zone: $zone,
            express: $express,
            createdAt: $createdAt,
            estimatedArrival: $estimatedArrival
        );

        $service->register($parcel);

        if (rand(1, 100) > 20) {
            $service->changeStatus($tracking, 'delivered');
        } elseif (rand(1, 100) > 10) {
            $service->changeStatus($tracking, 'in_transit');
        }
    } catch (\Throwable $e) {
    }
}

$service->register(new Parcel('YD101827922RU', 'Данияр Ахметов', 'ул. Абая, 15', ['Донер' => 1, 'Кола' => 1], 12.5, DeliveryZone::CITY));
$service->changeStatus('YD101827922RU', 'delivered');

$service->register(new Parcel('YD101827913RU', 'Айгерим Сериковна', 'пр. Достык, 105', ['Суши-сет' => 1], 15.0, DeliveryZone::REGION));
$service->changeStatus('YD101827913RU', 'delivered');

$service->register(new Parcel('YD101827904RU', 'Ерасыл Кайратович', 'ул. Сатпаева, 22/1', ['Капучино' => 2], 10.2, DeliveryZone::CITY));
$service->changeStatus('YD101827904RU', 'delivered');

$service->register(new Parcel('YD101827897RU', 'Мадина Бекова', 'мкр. Самал-2, 33', ['Чизбургер' => 2], 8.5, DeliveryZone::CITY));
$service->changeStatus('YD101827897RU', 'delivered');

$service->register(new Parcel('YD101827894RU', 'Алишер Нурланов', 'ул. Желтоксан, 8', ['Пицца' => 1], 7.0, DeliveryZone::CITY));
$service->changeStatus('YD101827894RU', 'delivered');

$service->register(new Parcel('YD101827890RU', 'Жанна Ахметова', 'пр. Аль-Фараби, 77', ['Латте' => 1], 6.8, DeliveryZone::CITY));
$service->changeStatus('YD101827890RU', 'delivered');

function printMenu(): void
{
    echo "\n";
    echo "==============================================\n";
    echo "     СИСТЕМА ДОСТАВКИ ПОСЫЛОК (Яндекс)\n";
    echo "==============================================\n";
    echo "  1. Создать заказ вручную\n";
    echo "  2. Показать все заказы (история)\n";
    echo "  3. Отследить заказ (статус + время)\n";
    echo "  4. Изменить статус заказа\n";
    echo "  5. Назначить курьера\n";
    echo "  6. История заказов (все события)\n";
    echo "  7. Доход (выручка)\n";
    echo "  8. Посылки тяжелее 10 кг\n";
    echo "  9. Экспресс-посылки\n";
    echo "  0. Сгенерировать 5 случайных заказов\n";
    echo "  q. Выход\n";
    echo "==============================================\n";
    echo "Выберите действие: ";
}

function input(string $prompt): string
{
    echo $prompt;
    return trim((string) fgets(STDIN));
}

function formatPrice(int $amount): string
{
    $kopecks = rand(10, 99);
    return number_format($amount, 0, '', ' ') . ",{$kopecks} KZT";
}

function printParcelRow(Parcel $p, DeliveryService $service, array $senders): void
{
    $sender = $senders[array_rand($senders)];
    $price = formatPrice($service->calculateTariff($p->tracking));
    $createdTime = date('H:i', strtotime($p->createdAt));
    $deliveredTime = $p->deliveredAt ? date('H:i', strtotime($p->deliveredAt)) : '--:--';
    $duration = '';
    if ($p->deliveredAt) {
        $diff = strtotime($p->deliveredAt) - strtotime($p->createdAt);
        $minutes = round($diff / 60);
        $duration = " (в пути: {$minutes} мин)";
    }

    $itemsStr = [];
    foreach ($p->items as $name => $qty) {
        $itemsStr[] = "{$name} x{$qty}";
    }
    $itemsLine = implode(', ', $itemsStr);

    echo "  {$sender} {$price}\n";
    echo "  {$createdTime} - {$deliveredTime}{$duration}\n";
    echo "  Адрес: {$p->address}\n";
    echo "  Заказ: {$itemsLine}\n";
    echo "  Трек: {$p->tracking} | Получатель: {$p->recipient} | Зона: {$p->zone->label()} | Вес: {$p->weightKg} кг\n";
    echo "  ----------------------------------------------------------\n";
}

while (true) {
    printMenu();
    $choice = input('');

    if (strtolower($choice) === 'q') {
        echo "\nДо свидания!\n";
        break;
    }

    switch ($choice) {
        case '1':
            echo "\n--- СОЗДАНИЕ ЗАКАЗА ---\n";
            $tracking = input('Трек-номер (YD123456789RU): ');
            $recipient = input('Получатель: ');
            $address = input('Адрес доставки: ');
            $itemsStr = input('Товары (через запятую): ');
            $items = [];
            foreach (explode(',', $itemsStr) as $item) {
                $items[trim($item)] = 1;
            }
            $weight = (float) input('Вес (кг): ');
            echo "Зона: 1 - Город, 2 - Область, 3 - Межрегион: ";
            $zoneChoice = input('');
            $zone = match ($zoneChoice) {
                '2' => DeliveryZone::REGION,
                '3' => DeliveryZone::FEDERAL,
                default => DeliveryZone::CITY,
            };
            $express = strtolower(input('Экспресс? (y/n): ')) === 'y';

            try {
                $service->register(new Parcel($tracking, $recipient, $address, $items, $weight, $zone, express: $express));
                echo "Заказ создан. Ожидаемое прибытие: {$repository->findByTracking($tracking)?->estimatedArrival}\n";
            } catch (\Throwable $e) {
                echo "Ошибка: {$e->getMessage()}\n";
            }
            break;

        case '2':
            echo "\n--- ВСЕ ЗАКАЗЫ ---\n";
            foreach (array_reverse($repository->all()) as $p) {
                printParcelRow($p, $service, $senders);
            }
            break;

        case '3':
            $tracking = input('Трек-номер: ');
            $p = $repository->findByTracking($tracking);
            if ($p === null) {
                echo "Заказ не найден.\n";
                break;
            }
            echo "\n--- ОТСЛЕЖИВАНИЕ ---\n";
            printParcelRow($p, $service, $senders);
            break;

        case '4':
            $tracking = input('Трек-номер: ');
            echo "Новый статус (registered / in_transit / delivered): ";
            $status = input('');
            try {
                $service->changeStatus($tracking, $status);
                echo "Статус изменён на '{$status}'.\n";
                if ($status === 'delivered') {
                    $p = $repository->findByTracking($tracking);
                    echo "Время получения: " . date('H:i', strtotime($p->deliveredAt)) . "\n";
                }
            } catch (\Throwable $e) {
                echo "Ошибка: {$e->getMessage()}\n";
            }
            break;

        case '5':
            $tracking = input('Трек-номер: ');
            $courierId = input('ID курьера (C001): ');
            $courierName = input('Имя курьера: ');
            echo "Зона курьера: 1 - Город, 2 - Область, 3 - Межрегион: ";
            $zoneChoice = input('');
            $zone = match ($zoneChoice) {
                '2' => DeliveryZone::REGION,
                '3' => DeliveryZone::FEDERAL,
                default => DeliveryZone::CITY,
            };
            try {
                $service->assignCourier($tracking, new Courier($courierId, $courierName, $zone));
                echo "Курьер назначен.\n";
            } catch (\Throwable $e) {
                echo "Ошибка: {$e->getMessage()}\n";
            }
            break;

        case '6':
            echo "\n--- ИСТОРИЯ ВСЕХ ЗАКАЗОВ ---\n";
            foreach ($repository->all() as $p) {
                $createdTime = date('H:i', strtotime($p->createdAt));
                $deliveredTime = $p->deliveredAt ? date('H:i', strtotime($p->deliveredAt)) : 'в процессе';
                echo "\n{$p->tracking} ({$p->recipient})\n";
                echo "  Адрес: {$p->address}\n";
                echo "  Заказ: " . implode(', ', array_map(fn($k, $v) => "{$k} x{$v}", array_keys($p->items), $p->items)) . "\n";
                echo "  Оформлен: {$createdTime}\n";
                echo "  Получен: {$deliveredTime}\n";
                echo "  Сумма: " . formatPrice($service->calculateTariff($p->tracking)) . "\n";
                echo "  Статус: {$p->status}\n";
            }
            break;

        case '7':
            echo "\n--- ДОХОД ---\n";
            printf("Общая выручка: %s\n", formatPrice($service->totalRevenue()));
            break;

        case '8':
            echo "\n--- ТЯЖЕЛЕЕ 10 КГ ---\n";
            foreach ($service->findHeavierThan(10.0) as $p) {
                printParcelRow($p, $service, $senders);
            }
            break;

        case '9':
            echo "\n--- ЭКСПРЕСС-ПОСЫЛКИ ---\n";
            foreach ($service->expressParcels() as $p) {
                printParcelRow($p, $service, $senders);
            }
            break;

        case '0':
            echo "\n--- ГЕНЕРАЦИЯ 5 НОВЫХ ЗАКАЗОВ ---\n";
            for ($i = 0; $i < 5; $i++) {
                generateRandomOrder($service, $recipients, $addresses, $productsList);
            }
            echo "Сгенерировано 5 новых заказов!\n";
            break;

        default:
            echo "Неизвестная команда.\n";
    }
}