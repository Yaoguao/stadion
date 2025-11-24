<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ошибка кодировки</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f3f4f6;
        }
        .error-container {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        h1 {
            color: #dc2626;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Ошибка кодировки UTF-8</h1>
        <p>{{ $message }}</p>
        <p><small>Проверьте переменные окружения и данные в базе данных на наличие некорректных символов.</small></p>
    </div>
</body>
</html>

