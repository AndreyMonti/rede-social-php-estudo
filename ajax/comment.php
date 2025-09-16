<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');
if(!isLogged()) { echo json_encode(['error'=>'login']); exit; }
$post_id = intval($_POST['post_id'] ?? 0);
$conteudo = trim($_POST['conteudo'] ?? '');
if(!$conteudo) { echo json_encode(['error'=>'empty']); exit; }
$stmt = $pdo->prepare('INSERT INTO comments (user_id,post_id,conteudo) VALUES (?,?,?)');
$stmt->execute([currentUserId(), $post_id, $conteudo]);
$cid = $pdo->lastInsertId();
$stmt = $pdo->prepare('SELECT c.*, u.nome, u.avatar FROM comments c JOIN users u ON u.id=c.user_id WHERE c.id=?');
$stmt->execute([$cid]);
$comment = $stmt->fetch();
echo json_encode(['status'=>'ok','comment'=>$comment]);
