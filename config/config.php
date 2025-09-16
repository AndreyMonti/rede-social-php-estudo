<?php
$db_host = 'localhost';
$db_name = 'redesocial_db';
$db_user = 'root';
$db_pass = '';

define('BASE_URL', '/rede-social-php-estudo'); 

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, $options);
} catch (Exception $e) {
    die('Erro DB: ' . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLogged() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}
