<?php

$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'blog_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "Подключение к базе данных установлено\n";
    
    $pdo->exec("DELETE FROM comments");
    $pdo->exec("DELETE FROM posts");
    echo "Существующие данные очищены\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0'
            ],
            'timeout' => 30
        ]
    ]);
    
    echo "Загрузка записей блога...\n";
    $postsData = file_get_contents('https://jsonplaceholder.typicode.com/posts', false, $context);
    
    if ($postsData === false) {
        throw new Exception("Ошибка при получении данных записей");
    }
    
    $posts = json_decode($postsData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Ошибка декодирования JSON записей: " . json_last_error_msg());
    }
    
    echo "JSON posts распарсен, количество записей: " . count($posts) . "\n";
    
    $postStmt = $pdo->prepare("INSERT INTO posts (id, user_id, title, body) VALUES (?, ?, ?, ?)");
    
    $postsCount = 0;
    
    foreach ($posts as $post) {
        $postStmt->execute([
            $post['id'],
            $post['userId'],
            $post['title'],
            $post['body']
        ]);
        $postsCount++;
    }
    
    echo "Записи блога загружены: {$postsCount}\n";
    
    echo "Загрузка комментариев...\n";
    $commentsData = file_get_contents('https://jsonplaceholder.typicode.com/comments', false, $context);
    
    if ($commentsData === false) {
        throw new Exception("Ошибка при получении данных комментариев");
    }
    
    $comments = json_decode($commentsData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Ошибка декодирования JSON комментариев: " . json_last_error_msg());
    }
    
    echo "JSON comments распарсен, количество записей: " . count($comments) . "\n";
    
    $commentStmt = $pdo->prepare("INSERT INTO comments (id, post_id, name, email, body) VALUES (?, ?, ?, ?, ?)");
    
    $commentsCount = 0;
    
    foreach ($comments as $comment) {
        $commentStmt->execute([
            $comment['id'],
            $comment['postId'],
            $comment['name'],
            $comment['email'],
            $comment['body']
        ]);
        $commentsCount++;
    }
    
    echo "Комментарии загружены: {$commentsCount}\n";
    
    echo "\n=== РЕЗУЛЬТАТ ЗАГРУЗКИ ===\n";
    echo "Загружено {$postsCount} записей и {$commentsCount} комментариев\n";
    echo "Загрузка завершена успешно!\n";
    
} catch (PDOException $e) {
    echo "Ошибка базы данных: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
