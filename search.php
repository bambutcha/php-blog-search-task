<?php

$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'blog_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

$searchResults = [];
$searchQuery = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchQuery = trim($_POST['search']);
    
    if (strlen($searchQuery) < 3) {
        $errorMessage = 'Поисковый запрос должен содержать минимум 3 символа';
    } else {
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            $sql = "
                SELECT DISTINCT 
                    p.id as post_id,
                    p.title as post_title,
                    c.id as comment_id,
                    c.name as comment_name,
                    c.body as comment_body,
                    c.email as comment_email
                FROM posts p
                INNER JOIN comments c ON p.id = c.post_id
                WHERE c.body LIKE :search
                ORDER BY p.id ASC, c.id ASC
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':search' => '%' . $searchQuery . '%']);
            $searchResults = $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $errorMessage = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поиск записей по комментариям</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .search-form {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        input[type="text"]:focus {
            border-color: #0066cc;
            outline: none;
        }
        
        button {
            background-color: #0066cc;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #0052a3;
        }
        
        .error {
            background-color: #ffe6e6;
            color: #cc0000;
            padding: 10px;
            border-radius: 3px;
            margin-bottom: 20px;
            border: 1px solid #ffcccc;
        }
        
        .results-header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .results-count {
            color: #666;
            font-size: 14px;
        }
        
        .result-item {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 3px;
        }
        
        .post-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .comment-info {
            background-color: #f8f8f8;
            padding: 10px;
            border-left: 3px solid #0066cc;
            margin-top: 10px;
        }
        
        .comment-meta {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }
        
        .comment-body {
            color: #333;
        }
        
        .highlight {
            background-color: #ffff99;
            padding: 1px 2px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Поиск записей блога</h1>
        <p class="subtitle">Поиск записей по тексту комментариев (минимум 3 символа)</p>
        
        <form method="POST" class="search-form">
            <label for="search">Поисковый запрос:</label>
            <input 
                type="text" 
                id="search" 
                name="search" 
                value="<?= htmlspecialchars($searchQuery) ?>"
                placeholder="Введите текст для поиска..."
                minlength="3"
                required
            >
            <button type="submit">Найти</button>
        </form>
        
        <?php if ($errorMessage): ?>
            <div class="error">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errorMessage): ?>
            <div class="results-header">
                <h2>Результаты поиска</h2>
                <div class="results-count">
                    <?php if (empty($searchResults)): ?>
                        Записи не найдены
                    <?php else: ?>
                        Найдено: <?= count($searchResults) ?> результатов по запросу "<?= htmlspecialchars($searchQuery) ?>"
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (empty($searchResults)): ?>
                <div class="no-results">
                    <p>Ничего не найдено</p>
                    <p>Попробуйте изменить поисковый запрос</p>
                </div>
            <?php else: ?>
                <?php 
                $groupedResults = [];
                foreach ($searchResults as $result) {
                    $postId = $result['post_id'];
                    if (!isset($groupedResults[$postId])) {
                        $groupedResults[$postId] = [
                            'post_title' => $result['post_title'],
                            'comments' => []
                        ];
                    }
                    $groupedResults[$postId]['comments'][] = $result;
                }
                ?>
                
                <?php foreach ($groupedResults as $postId => $postData): ?>
                    <div class="result-item">
                        <div class="post-title">
                            <?= htmlspecialchars($postData['post_title']) ?>
                        </div>
                        
                        <?php foreach ($postData['comments'] as $comment): ?>
                            <div class="comment-info">
                                <div class="comment-meta">
                                    Автор: <?= htmlspecialchars($comment['comment_name']) ?> 
                                    (<?= htmlspecialchars($comment['comment_email']) ?>)
                                </div>
                                <div class="comment-body">
                                    <?php
                                    $highlightedText = str_ireplace(
                                        $searchQuery,
                                        '<span class="highlight">' . htmlspecialchars($searchQuery) . '</span>',
                                        htmlspecialchars($comment['comment_body'])
                                    );
                                    echo $highlightedText;
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="footer">
            <p>Тестовое задание PHP Junior | Даниил Сергеевич</p>
        </div>
    </div>

    <script>
        document.getElementById('search').focus();
    </script>
</body>
</html>
