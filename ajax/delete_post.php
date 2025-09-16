<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
    echo json_encode(['status'=>'error','msg'=>'Você precisa estar logado']);
    exit;
}

$post_id = intval($_POST['post_id'] ?? 0);
if(!$post_id){
    echo json_encode(['status'=>'error','msg'=>'Post inválido']);
    exit;
}

// Verificar se o post pertence ao usuário
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id=? AND user_id=?");
$stmt->execute([$post_id, $user_id]);
$post = $stmt->fetch();

if(!$post){
    echo json_encode(['status'=>'error','msg'=>'Post não encontrado ou sem permissão']);
    exit;
}

// Excluir imagem do servidor se existir
if($post['imagem'] && file_exists(__DIR__.'/../'.$post['imagem'])){
    unlink(__DIR__.'/../'.$post['imagem']);
}

// Excluir post
$stmt = $pdo->prepare("DELETE FROM posts WHERE id=?");
$stmt->execute([$post_id]);

echo json_encode(['status'=>'ok']);
