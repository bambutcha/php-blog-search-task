<?php

echo "=== АВТОМАТИЧЕСКАЯ УСТАНОВКА BLOG SEARCH ===\n\n";

$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'blog_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    echo "Шаг 1: Создание базы данных...\n";
    
    $dsn = "mysql:host={$dbConfig['host']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "База данных '{$dbConfig['dbname']}' создана/проверена\n";
    
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Подключение к базе данных установлено\n\n";
    
    echo "Шаг 2: Создание таблиц...\n";
    
    $pdo->exec("DROP TABLE IF EXISTS comments");
    $pdo->exec("DROP TABLE IF EXISTS posts");
    
    $sql = file_get_contents('database.sql');
    if ($sql === false) {
        throw new Exception("Не удалось прочитать файл database.sql");
    }
    
    $pdo->exec($sql);
    echo "Таблицы и индексы созданы\n\n";
    
    echo "Шаг 3: Загрузка данных...\n";
    
    ob_start();
    include 'load_data.php';
    $output = ob_get_clean();
    
    $lines = explode("\n", $output);
    $filteredLines = array_filter($lines, function($line) {
        return !str_contains($line, 'Подключение к базе данных установлено') &&
               !str_contains($line, 'Существующие данные очищены');
    });
    
    echo implode("\n", $filteredLines);
    
} catch (PDOException $e) {
    echo "ОШИБКА базы данных: " . $e->getMessage() . "\n";
    echo "\nПроверьте:\n";
    echo "- Запущен ли MySQL сервер\n";
    echo "- Правильные ли настройки подключения в \$dbConfig\n";
    echo "- Есть ли права на создание базы данных\n";
    exit(1);
} catch (Exception $e) {
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    exit(1);
}
