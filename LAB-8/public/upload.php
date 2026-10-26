<?php
declare(strict_types=1);

// Подключаем файл с функцией appendMetadata
require_once __DIR__ . '/appendMetadata.php';

$storageDir = __DIR__ . '/../var/uploads';
$metaFile = __DIR__ . '/../var/data/submissions.json';
$userId = 'student_123';

if (!is_dir($storageDir) && !mkdir($storageDir, 0750, true)) {
    throw new RuntimeException("Не удалось создать каталог хранения");
}
$dataDir = dirname($metaFile);
if (!is_dir($dataDir) && !mkdir($dataDir, 0750, true)) {
    throw new RuntimeException("Не удалось создать каталог метаданных");
}

$file = $_FILES['report'] ?? null;
if (!is_array($file) || !isset($file['error'], $file['size'], $file['tmp_name'], $file['name'])) {
    throw new InvalidArgumentException("Некорректная структура загрузки");
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException("Ошибка загрузки файла, код: " . $file['error']);
}

if ($file['size'] < 1 || $file['size'] > 5 * 1024 * 1024) {
    throw new DomainException("Допустим размер файла от 1 байта до 5 MiB");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
if ($mime === false) {
    throw new RuntimeException("Не удалось определить тип файла");
}

$allowedMimeTypes = [
    'application/pdf' => 'pdf',
    'image/png' => 'png',
    'image/jpeg' => 'jpg'
];

$extension = $allowedMimeTypes[$mime] ?? null;
if ($extension === null) {
    throw new DomainException("Недопустимый тип файла. Разрешены только PDF, PNG и JPEG.");
}

$serverName = bin2hex(random_bytes(16)) . '.' . $extension;
$destination = $storageDir . DIRECTORY_SEPARATOR . $serverName;

if (!is_uploaded_file($file['tmp_name'])) {
    throw new RuntimeException("Источник не является HTTP-загрузкой");
}

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    throw new RuntimeException("Не удалось сохранить загруженный файл");
}

try {
    // Вызов функции из подключенного файла appendMetadata.php
    appendMetadata($metaFile, [
        'id' => bin2hex(random_bytes(8)),
        'ownerId' => $userId,
        'originalName' => basename($file['name']),
        'serverName' => $serverName,
        'mime' => $mime,
        'size' => $file['size'],
        'uploadedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
    ]);
    echo json_encode(['success' => true, 'message' => 'Файл успешно загружен'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    @unlink($destination);
    throw $e;
}