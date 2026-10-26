<?php
declare(strict_types=1);

function loadJsonList(string $path): array
{
    if (!file_is_accessible($path) && !file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Не удалось прочитать файл");
    }

    if (trim($content) === '') {
        return [];
    }

    try {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException("Поврежден формат JSON-файла", 0, $e);
    }

    if (!is_array($data) || !array_is_list($data)) {
        throw new UnexpectedValueException("Структура данных не является списком");
    }

    return $data;
}

function file_is_accessible(string $path): bool {
    return file_exists($path) && is_readable($path);
}