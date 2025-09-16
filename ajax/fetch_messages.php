<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');
if(!isLogged()) { echo json_encode(['error'=>'login']); exit; }
$other = intval($_GET['other'] ?? 0);
$me = currentUserId();
$stmt = $pdo->prepare('SELECT id FROM conversations WHERE (user_a=? AND user_b=?) OR (user_a=? AND user_b=?)');
$stmt->execute([$me,$other,$other,$me]);
$conv = $stmt->fetch();
if(!$conv) { echo json_encode(['messages'=>[]]); exit; }
$stmt = $pdo->prepare('SELECT m.*, u.nome FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.conversation_id=? ORDER BY m.criado_em ASC');
$stmt->execute([$conv['id']]);
$messages = $stmt->fetchAll();
echo json_encode(['messages'=>$messages]);
