<?php
declare(strict_types=1);
require dirname(__DIR__) . "/src/session.php";
requireLogin();

$allowedLangs = ["ru", "kk", "en"];
$lang = $_COOKIE["lang"] ?? "ru";
if (!is_string($lang) || !in_array($lang, $allowedLangs, true)) {
    $lang = "ru";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["lang"])) {
    $langValue = $_POST["lang"] ?? null;
    $token = $_POST["csrf_token"] ?? null;

    if (
        is_string($langValue) &&
        in_array($langValue, $allowedLangs, true) &&
        is_string($token) &&
        hash_equals($_SESSION["csrf_token"], $token)
    ) {
        setcookie("lang", $langValue, [
            "expires" => time() + 2592000,
            "path" => "/",
            "secure" => $isHttps,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
        header("Location: /dashboard.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="<?= h($lang) ?>">
<head><meta charset="UTF-8"><title>Кабинет - Стипендиальные заявки</title></head>
<body>
<h1>Личный кабинет</h1>
<p><strong>Пользователь:</strong> <?= h((string) $_SESSION["email"]) ?></p>
<p><strong>Роль:</strong> <?= h((string) $_SESSION["role"]) ?></p>

<hr>

<?php if ($_SESSION["role"] === "applicant"): ?>
    <h2>Панель заявителя</h2>
    <p>Статус вашей заявки на стипендию: <strong>На рассмотрении</strong></p>
<?php elseif ($_SESSION["role"] === "secretary"): ?>
    <h2>Панель секретаря</h2>
    <p style="color: green;">[Защищенное действие]: Доступ к проверке документов студентов открыт.</p>
    <ul>
        <li>Заявка #101 (applicant@atu.edu.kz) — Документы проверены</li>
        <li>Заявка #102 (student2@atu.edu.kz) — Ожидает проверки</li>
    </ul>
<?php endif; ?>

<hr>

<h3>Настройка интерфейса (Cookie)</h3>
<form method="post">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
    <label>Язык: 
        <select name="lang">
            <option value="ru" <?= $lang === "ru" ? "selected" : "" ?>>Русский</option>
            <option value="kk" <?= $lang === "kk" ? "selected" : "" ?>>Қазақша</option>
            <option value="en" <?= $lang === "en" ? "selected" : "" ?>>English</option>
        </select>
    </label>
    <button type="submit">Сохранить язык</button>
</form>

<br>
<form method="post" action="/logout.php">
    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
    <button type="submit" style="color: red;">Выйти из системы</button>
</form>
</body>
</html>

