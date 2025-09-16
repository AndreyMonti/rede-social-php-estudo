<?php
require_once __DIR__ . '/../config/config.php';
?>
<header class="topbar">
  <a href="<?= BASE_URL ?>/public/feed.php" class="logo">ConectaTech</a>
  <nav class="topbar-nav">
    <?php if(isLogged()): ?>
  <a href="/public/profile.php?id=<?=currentUserId()?>" class="nav-link">Meu Perfil</a>
      <a href="<?= BASE_URL ?>/public/chat.php" class="nav-link">Chat</a>
      <a href="<?= BASE_URL ?>/public/logout.php" class="nav-link">Sair</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/public/login.php" class="nav-link">Entrar</a>
      <a href="<?= BASE_URL ?>/public/register.php" class="nav-link">Cadastrar</a>
    <?php endif; ?>
  </nav>
</header>
<style>
  .topbar {
    background: linear-gradient(90deg, #4f8cff 0%, #6c63ff 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 32px;
    box-shadow: 0 2px 8px rgba(76, 110, 245, 0.08);
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo {
    font-size: 2rem;
    font-weight: bold;
    letter-spacing: 1px;
    text-decoration: none;
    color: #fff;
    transition: color 0.2s;
  }
  .logo:hover {
    color: #ffd700;
  }
  .topbar-nav {
    display: flex;
    gap: 24px;
  }
  .nav-link {
    color: #fff;
    text-decoration: none;
    font-size: 1.1rem;
    font-weight: 500;
    padding: 8px 16px;
    border-radius: 6px;
    transition: background 0.2s, color 0.2s;
  }
  .nav-link:hover {
    background: rgba(255,255,255,0.15);
    color: #ffd700;
  }
  @media (max-width: 600px) {
    .topbar {
      flex-direction: column;
      align-items: flex-start;
      padding: 12px 16px;
    }
    .topbar-nav {
      gap: 12px;
      flex-wrap: wrap;
    }
    .logo {
      font-size: 1.5rem;
    }
  }
</style>
