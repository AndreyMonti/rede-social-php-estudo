<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_GET['id'])) {
    die("Post não encontrado.");
}
$postId = (int) $_GET['id'];

// Buscar post
$stmt = $pdo->prepare("SELECT p.*, u.nome, u.avatar FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=?");
$stmt->execute([$postId]);
$post = $stmt->fetch();
if(!$post) die("Post não existe.");

// Inserir comentário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLogged() && !empty($_POST['comentario'])) {
    $conteudo = trim($_POST['comentario']);
    $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, conteudo) VALUES (?, ?, ?)");
    $stmt->execute([currentUserId(), $postId, $conteudo]);
    header("Location: post.php?id=" . $postId);
    exit;
}

$stmt = $pdo->prepare("SELECT c.*, u.nome, u.avatar FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.criado_em ASC");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();
?>

<div class="container">
  <article class="post">
    <div class="meta">
      <img class="avatar" src="<?= $post['avatar'] ? '/public/uploads/'.esc($post['avatar']) : '/assets/img/default-avatar.png' ?>" alt="">
      <strong><?= esc($post['nome']) ?></strong>
      <time><?= esc($post['criado_em']) ?></time>
    </div>
    <div class="content">
      <p><?= nl2br(esc($post['conteudo'])) ?></p>
      <?php if($post['imagem']): ?>
        <img src="/public/uploads/<?= esc($post['imagem']) ?>" class="post-img">
      <?php endif; ?>
    </div>
  </article>

  <h3>Comentários</h3>
  <?php if(isLogged()): ?>
    <form method="post">
      <textarea name="comentario" rows="2" placeholder="Escreva um comentário..."></textarea>
      <button type="submit">Comentar</button>
    </form>
  <?php endif; ?>

  <?php foreach($comments as $c): ?>
    <div class="comment">
      <img class="avatar" src="<?= $c['avatar'] ? '/public/uploads/'.esc($c['avatar']) : '/assets/img/default-avatar.png' ?>" alt="">
      <strong><?= esc($c['nome']) ?>:</strong>
      <span><?= nl2br(esc($c['conteudo'])) ?></span>
      <time><?= esc($c['criado_em']) ?></time>
    </div>
  <?php endforeach; ?>
</div>
