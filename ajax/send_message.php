<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');
if(!isLogged()) { echo json_encode(['error'=>'login']); exit; }
$to = intval($_POST['to']);
$texto = trim($_POST['texto'] ?? '');
if(!$texto) { echo json_encode(['error'=>'empty']); exit; }
$me = currentUserId();
$stmt = $pdo->prepare('SELECT id FROM conversations WHERE (user_a=? AND user_b=?) OR (user_a=? AND user_b=?)');
$stmt->execute([$me,$to,$to,$me]);
$conv = $stmt->fetch();
if(!$conv){
  $pdo->prepare('INSERT INTO conversations (user_a,user_b) VALUES (?,?)')->execute([$me,$to]);
  $convId = $pdo->lastInsertId();
} else { $convId = $conv['id']; }
$pdo->prepare('INSERT INTO messages (conversation_id, sender_id, texto) VALUES (?,?,?)')->execute([$convId, $me, $texto]);
echo json_encode(['status'=>'ok']);
