<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if(!isLogged()) header('Location: /public/login.php');
$pageTitle='Post';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, u.nome, u.avatar FROM posts p JOIN users u ON u.id=p.user_id WHERE p.id=?');
$stmt->execute([$id]);
$p = $stmt->fetch();
if(!$p) { echo '<div class="container">Post não encontrado</div>'; include __DIR__ . '/../includes/footer.php'; exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['comentario'])){
    $conteudo = trim($_POST['comentario']);
    if($conteudo){
        $stmt = $pdo->prepare('INSERT INTO comments (user_id,post_id,conteudo) VALUES (?,?,?)');
        $stmt->execute([currentUserId(), $id, $conteudo]);
        // redireciona corretamente usando BASE_URL
        header('Location: ' . BASE_URL . '/public/post.php?id=' . $id);
        exit;
    }
}


$stmt = $pdo->prepare('SELECT c.*, u.nome, u.avatar FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.criado_em ASC');
$stmt->execute([$id]);
$comments = $stmt->fetchAll();
?>
<div class="container">
  <article class="post">
    <div class="meta">
      <img class="avatar" src="<?= $p['avatar'] ? '/public/uploads/' . esc($p['avatar']) : '/assets/img/default-avatar.png' ?>"> 
      <strong><?=esc($p['nome'])?></strong>
      <time><?=esc($p['criado_em'])?></time>
    </div>
    <div class="content">
      <p><?=nl2br(esc($p['conteudo']))?></p>
      <?php if($p['imagem']): ?>
        <img class="post-img" src="/public/uploads/<?=esc($p['imagem'])?>">
      <?php endif; ?>
    </div>
  </article>

  <section class="comments">
    <h3>Comentários</h3>
    <form method="post">
      <textarea name="comentario" required></textarea>
      <button>Comentar</button>
    </form>
    <?php foreach($comments as $c): ?>
      <div class="comment">
        <img class="avatar" src="<?= $c['avatar'] ? '/public/uploads/' . esc($c['avatar']) : '/assets/img/default-avatar.png' ?>">
        <strong><?=esc($c['nome'])?></strong>
        <p><?=nl2br(esc($c['conteudo']))?></p>
        <time><?=esc($c['criado_em'])?></time>
      </div>
    <?php endforeach; ?>
  </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
