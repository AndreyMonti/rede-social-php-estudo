<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
// Buscar usuário
$id = intval($_GET['id'] ?? currentUserId());
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch();
if(!$u) { echo 'Usuário não encontrado'; exit; }
$pageTitle = 'Perfil - ' . $u['nome'];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';

// Buscar número de posts
$stmtPosts = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id=?');
$stmtPosts->execute([$id]);
$numPosts = $stmtPosts->fetchColumn();

// Buscar número de likes recebidos
$stmtLikes = $pdo->prepare('SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id=?');
$stmtLikes->execute([$id]);
$numLikes = $stmtLikes->fetchColumn();
?>
<div class="profile-card">
  <div class="profile-avatar">
    <img src="<?= $u['avatar'] ? '/public/uploads/' . esc($u['avatar']) : '/assets/img/default-avatar.png' ?>" alt="Avatar de <?=esc($u['nome'])?>">
  </div>
  <div class="profile-info">
    <h1 class="profile-name"><?=esc($u['nome'])?></h1>
    <p class="profile-bio"><?=nl2br(esc($u['bio']))?></p>
    <div class="profile-stats">
      <div><strong>Posts:</strong> <?= $numPosts ?></div>
      <div><strong>Likes:</strong> <?= $numLikes ?></div>
      <div><strong>Desde:</strong> <?=date('M/Y', strtotime($u['created_at'] ?? ''))?></div>
    </div>
  </div>
</div>
<style>
  .profile-card {
    max-width: 400px;
    margin: 32px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(76,110,245,0.08);
    padding: 32px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
  }
  .profile-avatar img {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #6c63ff;
    background: #f3f3f3;
  }
  .profile-name {
    font-size: 2rem;
    font-weight: bold;
    margin: 0;
    color: #4f8cff;
    text-align: center;
  }
  .profile-bio {
    font-size: 1.1rem;
    color: #444;
    text-align: center;
    margin: 8px 0 0 0;
    white-space: pre-line;
  }
  .profile-stats {
    display: flex;
    gap: 24px;
    margin-top: 16px;
    font-size: 1rem;
    color: #333;
    justify-content: center;
  }
  .profile-stats div {
    background: #f5f7ff;
    border-radius: 8px;
    padding: 8px 12px;
    min-width: 70px;
    text-align: center;
  }
  @media (max-width: 600px) {
    .profile-card {
      padding: 16px 8px;
    }
    .profile-avatar img {
      width: 80px;
      height: 80px;
    }
    .profile-name {
      font-size: 1.3rem;
    }
    .profile-stats {
      gap: 8px;
      font-size: 0.95rem;
    }
  }
</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
