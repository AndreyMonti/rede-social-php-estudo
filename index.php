<?php
require_once __DIR__ . '/config/config.php';

// se estiver logado, redireciona para feed
if (isLogged()) {
    header('Location: ' . BASE_URL . '/public/feed.php');
    exit;
}

// caso contrário, para a tela de login
header('Location: ' . BASE_URL . '/public/login.php');
exit;
