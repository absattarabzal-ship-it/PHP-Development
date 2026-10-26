<?php
declare(strict_types=1);

function processLargeFile(string $filePath): void
{
    $handle = fopen($filePath, 'rb');
    if ($handle === false) {
        throw new RuntimeException("Не удалось открыть файл для чтения");
    }

    try {
        while (($line = fgets($handle)) !== false) {
            // Обработка каждой строки по отдельности
            $cleanLine = rtrim($line, "\r\n");
            // processLine($cleanLine);
        }

        if (!feof($handle)) {
            throw new RuntimeException("Ошибка чтения файла: неожиданный обрыв потока");
        }
    } finally {
        fclose($handle);
    }
}