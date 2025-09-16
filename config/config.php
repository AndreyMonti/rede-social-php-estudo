<?php
// config/config.php - ajuste as credenciais conforme necessário para seu ambiente local
$db_host = 'localhost';
$db_name = 'redesocial_db';
$db_user = 'root';
$db_pass = '';

// --- ADICIONE ESTA LINHA: adapte se seu projeto estiver em outra pasta ---
define('BASE_URL', '/rede-social-php'); // sem barra final

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
