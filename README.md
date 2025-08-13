# Blog Search

Веб-приложение для поиска записей блога по тексту комментариев.

## Требования

- PHP 7.4+
- MySQL 5.7+
- Расширения: PDO, PDO_MySQL

## Установка

### Вариант 1: Автоматическая установка

```bash
php setup.php
```

### Вариант 2: Ручная установка

1. Создание базы данных:
```bash
mysql -u root -p
```
```sql
CREATE DATABASE blog_test;
USE blog_test;
SOURCE database.sql;
```

2. Загрузка данных:
```bash
php load_data.php
```

## Запуск

```bash
php -S localhost:8000
```

Откройте: http://localhost:8000/search.php

## Использование

- Введите минимум 3 символа для поиска
- Поиск ведется по тексту комментариев
- Результаты показывают заголовок записи и найденные комментарии

## Файлы

- `setup.php` - автоматическая установка
- `database.sql` - схема БД
- `load_data.php` - загрузка данных
- `search.php` - веб-интерфейс

## Настройка БД

Измените параметры в файлах при необходимости:

```php
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'blog_test',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```