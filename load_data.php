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
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Подключение к базе данных установлено\n";
    
    $pdo->exec("DELETE FROM comments");
    $pdo->exec("DELETE FROM posts");
    echo "Существующие данные очищены\n";
    
    echo "Загрузка записей блога из posts.json...\n";
    
    if (!file_exists('posts.json')) {
        throw new Exception("Файл posts.json не найден");
    }
    
    $postsData = file_get_contents('posts.json');
    
    if ($postsData === false) {
        throw new Exception("Не удалось прочитать posts.json");
    }
    
    echo "Размер posts.json: " . strlen($postsData) . " байт\n";
    
    $posts = json_decode($postsData, true);
    
    if ($posts === null) {
        throw new Exception("Ошибка парсинга posts.json: " . json_last_error_msg());
    }
    
    echo "Распарсено записей: " . count($posts) . "\n";
    
    $postStmt = $pdo->prepare("INSERT INTO posts (id, user_id, title, body) VALUES (?, ?, ?, ?)");
    
    $postsCount = 0;
    foreach ($posts as $post) {
        $postStmt->execute([$post['id'], $post['userId'], $post['title'], $post['body']]);
        $postsCount++;
    }
    
    echo "Записи блога загружены: {$postsCount}\n";
    
    echo "Загрузка комментариев из comments.json...\n";
    
    if (!file_exists('comments.json')) {
        throw new Exception("Файл comments.json не найден");
    }
    
    $commentsData = file_get_contents('comments.json');
    
    if ($commentsData === false) {
        throw new Exception("Не удалось прочитать comments.json");
    }
    
    echo "Размер comments.json: " . strlen($commentsData) . " байт\n";
    
    $comments = json_decode($commentsData, true);
    
    if ($comments === null) {
        throw new Exception("Ошибка парсинга comments.json: " . json_last_error_msg());
    }
    
    echo "Распарсено комментариев: " . count($comments) . "\n";
    
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
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}

?>
