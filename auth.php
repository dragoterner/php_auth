<?php
// Разрешённый ID (для примера)
$ALLOWED_ID = '150rub';

// Стартуем сессию
session_start();

// Получение ID пользователя из доступных источников
function getUserId(): ?string {
    // 1) GET-параметр ?uid=...
    if (isset($_GET['uid']) && $_GET['uid'] !== '') {
        return (string)$_GET['uid'];
    }

    // 2) данные из сессии
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] !== '') {
        return (string)$_SESSION['user_id'];
    }

    // 3) данные из куки
    if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] !== '') {
        return (string)$_COOKIE['user_id'];
    }

    return null;
}

// Получаем ID пользователя
$userId = getUserId();

// Базовая защита от перебора: лимит попыток
$LIMIT_ATTEMPTS = 5;

// Ключи счётчиков в сессии
if (!isset($_SESSION['uid_attempts'])) {
    $_SESSION['uid_attempts'] = 0;
}
if (!isset($_SESSION['uid_attempts_timestamp'])) {
    $_SESSION['uid_attempts_timestamp'] = time();
}

// Если ID не предоставлен — 403
if ($userId === null) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden: no user identifier provided.';
    exit;
}

// Увеличиваем счётчик попыток
$_SESSION['uid_attempts'] = intval($_SESSION['uid_attempts']) + 1;

// Блокировка после превышения лимита
if ($_SESSION['uid_attempts'] > $LIMIT_ATTEMPTS) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden: too many attempts.';
    exit;
}

// Основная проверка: совпадение IDs
if ($userId === $ALLOWED_ID) {
    // Доступ разрешён: показываем контент
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="utf-8">
        <title>Доступ разрешён</title>
    </head>
    <body>
        <h1>Добро пожаловать!</h1>
        <p>Контент доступен только для разрешённого пользователя.</p>
    </body>
    </html>
    <?php
    exit;
} else {
    // Доступ запрещён
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden: invalid user ID.';
    exit;
}
