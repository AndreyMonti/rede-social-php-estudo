<?php
require_once __DIR__ . '/../config/config.php';
?>
<header class="topbar">
  <a href="<?= BASE_URL ?>/public/feed.php" class="logo">ConectaTech</a>
  <nav>
    <?php if(isLogged()): ?>
      <a href="<?= BASE_URL ?>/public/profile.php?id=<?=currentUserId()?>">Meu Perfil</a>
      <a href="<?= BASE_URL ?>/public/chat.php">Chat</a>
      <a href="<?= BASE_URL ?>/public/logout.php">Sair</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/public/login.php">Entrar</a>
      <a href="<?= BASE_URL ?>/public/register.php">Cadastrar</a>
    <?php endif; ?>
  </nav>
</header>
