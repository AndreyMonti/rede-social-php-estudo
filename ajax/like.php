<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');
if(!isLogged()) { echo json_encode(['error'=>'login']); exit; }
$post_id = intval($_POST['post_id'] ?? 0);
$uid = currentUserId();
try{
  $stmt = $pdo->prepare('SELECT id FROM likes WHERE user_id=? AND post_id=?');
  $stmt->execute([$uid,$post_id]);
  $l = $stmt->fetch();
  if($l){
    $pdo->prepare('DELETE FROM likes WHERE id=?')->execute([$l['id']]);
    echo json_encode(['status'=>'unliked']);
  }else{
    $pdo->prepare('INSERT INTO likes (user_id,post_id) VALUES (?,?)')->execute([$uid,$post_id]);
    echo json_encode(['status'=>'liked']);
  }
} catch(Exception $e){ echo json_encode(['error'=>$e->getMessage()]); }
