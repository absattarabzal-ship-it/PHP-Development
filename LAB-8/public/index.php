<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Загрузка файлов</title>
</head>
<body>
    <h2>Загрузка отчета</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="report" required>
        <button type="submit">Загрузить</button>
    </form>
</body>
</html>