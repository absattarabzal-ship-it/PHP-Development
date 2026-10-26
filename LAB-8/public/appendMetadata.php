<?php
declare(strict_types=1);

function appendMetadata(string $path, array $record): void
{
    // Открываем файл в режиме чтения и записи с созданием при необходимости
    $handle = fopen($path, 'c+');
    if ($handle === false) {
        throw new RuntimeException("Не удалось открыть файл метаданных");
    }

    try {
        // Запрос эксклюзивной блокировки
        if (!flock($handle, LOCK_EX)) {
            throw new RuntimeException("Не удалось заблокировать файл метаданных");
        }

        rewind($handle);
        $json = stream_get_contents($handle);
        if ($json === false) {
            throw new RuntimeException("Не удалось прочитать метаданные");
        }

        // Декодирование существующего JSON с обязательнымброском исключений
        $records = trim($json) === ''
            ? []
            : json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($records) || !array_is_list($records)) {
            throw new UnexpectedValueException("Повреждена структура метаданных (ожидался список)");
        }

        // Добавление новой записи
        $records[] = $record;

        $output = json_encode(
            $records,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );

        // Перезапись файла новыми данными
        rewind($handle);
        if (!ftruncate($handle, 0) || fwrite($handle, $output) === false) {
            throw new RuntimeException("Не удалось обновить метаданные в файле");
        }

        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }
}