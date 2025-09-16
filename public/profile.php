<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
$id = intval($_GET['id'] ?? currentUserId());
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch();
if(!$u) { echo 'Usuário não encontrado'; exit; }
$pageTitle = 'Perfil - ' . $u['nome'];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <h1><?=esc($u['nome'])?></h1>
  <img src="<?= $u['avatar'] ? '/public/uploads/' . esc($u['avatar']) : '/assets/img/default-avatar.png' ?>" class="avatar-large">
  <p><?=nl2br(esc($u['bio']))?></p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
