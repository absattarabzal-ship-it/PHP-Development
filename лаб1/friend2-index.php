<?php
// === КОНСТАНТЫ ===
const UNIVERSITY = "Алматинский технологический университет";
const DISCIPLINE = "Программирование на PHP";
const BANK_NAME = "Kaspi Депозит";
const VARIANT = 9;

// === ДАННЫЕ СТУДЕНТА ===
$lastName = "Абдусаттар";
$firstName = "Абзал";
$group = "ИС-23-22";
$course = 3;
$studentName = $lastName . " " . $firstName;

// === ОБРАБОТКА ВВОДА ПОЛЬЗОВАТЕЛЯ ===
$deposit = isset($_POST['deposit']) ? floatval($_POST['deposit']) : 500000;
$interestRate = isset($_POST['interest_rate']) ? floatval($_POST['interest_rate']) : 14.0;
$months = isset($_POST['months']) ? intval($_POST['months']) : 12;

// Расчет дохода по месяцам
$years = $months / 12;
$income = $deposit * ($interestRate / 100) * $years;
$result = $deposit + $income;

if ($income > 0) {
    $status = "Депозит открыт и приносит доход";
    $statusClass = "success";
} else {
    $status = "Доход отсутствует";
    $statusClass = "warning";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaspi Депозит — Лабораторная работа №1</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
            color: #222;
        }
        .kaspi-container {
            max-width: 500px;
            margin: 20px auto;
        }
        .kaspi-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            margin-bottom: 16px;
        }
        .kaspi-header {
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 2px solid #f2f2f2;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .kaspi-logo {
            background-color: #f14635;
            color: white;
            font-weight: 800;
            font-size: 20px;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .kaspi-title h1 {
            font-size: 18px;
            margin: 0;
            color: #222;
        }
        .kaspi-title p {
            font-size: 13px;
            color: #777;
            margin: 2px 0 0 0;
        }
        .student-info {
            background-color: #f9f9f9;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            color: #555;
            margin-bottom: 20px;
        }
        .student-info strong {
            color: #222;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-size: 13px;
            color: #777;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input[type="number"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="number"]:focus {
            border-color: #f14635;
        }
        .radio-group {
            display: flex;
            gap: 8px;
        }
        .radio-group input {
            display: none;
        }
        .radio-group label {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #f2f2f2;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin: 0;
            transition: all 0.2s;
        }
        .radio-group input:checked + label {
            background: #f14635;
            color: white;
        }
        .btn-kaspi {
            width: 100%;
            background-color: #f14635;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .btn-kaspi:hover {
            background-color: #d93b2b;
        }
        .btn-reset {
            display: block;
            text-align: center;
            color: #777;
            font-size: 13px;
            text-decoration: none;
            margin-top: 12px;
            font-weight: 600;
        }
        .result-box {
            background: #fff8f7;
            border: 1px solid #ffe5e2;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
        }
        .result-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .result-row.total {
            font-size: 16px;
            font-weight: 700;
            border-top: 1px dashed #ffd0cb;
            padding-top: 8px;
            margin-top: 8px;
        }
        .success { color: #2e7d32; font-weight: 600; }
        .warning { color: #c62828; font-weight: 600; }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 16px;
        }
    </style>
</head>
<body>

<div class="kaspi-container">
    <div class="kaspi-card">
        <div class="kaspi-header">
            <div class="kaspi-logo">K</div>
            <div class="kaspi-title">
                <h1><?= BANK_NAME ?></h1>
                <p><?= DISCIPLINE ?> — Вариант №<?= VARIANT ?></p>
            </div>
        </div>

        <div class="student-info">
            <strong>Университет:</strong> <?= UNIVERSITY ?><br>
            <strong>Студент:</strong> <?= $studentName ?> (<?= $group ?>, <?= $course ?> курс)
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label for="deposit">Сумма депозита</label>
                <input type="number" id="deposit" name="deposit" step="1000" min="0" value="<?= htmlspecialchars($deposit) ?>" required>
            </div>

            <div class="form-group">
                <label for="interest_rate">Годовая эффективная ставка (%)</label>
                <input type="number" id="interest_rate" name="interest_rate" step="0.1" min="0" value="<?= htmlspecialchars($interestRate) ?>" required>
            </div>

            <div class="form-group">
                <label>Срок размещения</label>
                <div class="radio-group">
                    <input type="radio" id="m6" name="months" value="6" <?= $months == 6 ? 'checked' : '' ?>>
                    <label for="m6">6 мес</label>

                    <input type="radio" id="m12" name="months" value="12" <?= $months == 12 ? 'checked' : '' ?>>
                    <label for="m12">12 мес</label>

                    <input type="radio" id="m24" name="months" value="24" <?= $months == 24 ? 'checked' : '' ?>>
                    <label for="m24">24 мес</label>
                </div>
            </div>

            <button type="submit" class="btn-kaspi">Рассчитать доход</button>
            <a href="friend2-index.php" class="btn-reset">Сбросить параметры</a>
        </form>

        <div class="result-box">
            <div class="result-row">
                <span>Начисленный доход:</span>
                <strong>+<?= number_format($income, 0, '.', ' ') ?> ₸</strong>
            </div>
            <div class="result-row total">
                <span>Итоговая сумма:</span>
                <span style="color: #f14635;"><?= number_format($result, 0, '.', ' ') ?> ₸</span>
            </div>
            <div class="result-row" style="margin-top: 10px; margin-bottom: 0;">
                <span>Статус:</span>
                <span class="<?= $statusClass ?>"><?= $status ?></span>
            </div>
        </div>
    </div>

    <div class="footer">
        Дата формирования: <?= date("d.m.Y H:i") ?>
    </div>
</div>

</body>
</html>