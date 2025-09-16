<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if(!isLogged()) header('Location: /public/login.php');
$pageTitle='Feed';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['conteudo'])){
    $conteudo = trim($_POST['conteudo']);
    $imgName = null;
    if(!empty($_FILES['imagem']['name'])){
        $imgName = uploadImage($_FILES['imagem']);
    }
    if($conteudo || $imgName){
        $stmt = $pdo->prepare('INSERT INTO posts (user_id,conteudo,imagem) VALUES (?,?,?)');
        $stmt->execute([currentUserId(), $conteudo, $imgName]);
    }
    header('Location: ' . BASE_URL . '/public/feed.php');
    exit;

}

$stmt = $pdo->query('SELECT p.*, u.nome, u.avatar FROM posts p JOIN users u ON u.id=p.user_id ORDER BY p.criado_em DESC LIMIT 50');
$posts = $stmt->fetchAll();
?>
<div class="container">
  <h2>Compartilhar</h2>
  <form method="post" enctype="multipart/form-data">
    <textarea name="conteudo" placeholder="No que você está pensando?" rows="3"></textarea>
    <input type="file" name="imagem" accept="image/*">
    <button type="submit">Publicar</button>
  </form>

  <h2>Feed</h2>
  <?php foreach($posts as $p): ?>
    <article class="post">
      <div class="meta">
        <img class="avatar" src="<?= $p['avatar'] ? '/public/uploads/' . esc($p['avatar']) : '/assets/img/default-avatar.png' ?>" alt=""> 
        <strong><?=esc($p['nome'])?></strong>
        <time><?=esc($p['criado_em'])?></time>
      </div>
      <div class="content">
        <p><?=nl2br(esc($p['conteudo']))?></p>
        <?php if($p['imagem']): ?>
          <img class="post-img" src="/public/uploads/<?=esc($p['imagem'])?>" alt="post image">
        <?php endif; ?>
      </div>
      <div class="actions">
        <button class="like-btn" data-post="<?=$p['id']?>">Curtir</button>
        <a href="<?= BASE_URL ?>/public/post.php?id=<?= $p['id'] ?>">Comentários</a>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
