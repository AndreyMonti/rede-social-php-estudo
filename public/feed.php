<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Você precisa estar logado para acessar o feed.");
}

// Criar post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['conteudo'])) {
    $conteudo = $_POST['conteudo'];
    $imagem = '';

    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['imagem']['type'], $allowedTypes)) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $imagemNome = uniqid('img_') . '.' . $ext;
            $imagemPath = $uploadDir . $imagemNome;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath)) {
                $imagem = 'uploads/' . $imagemNome;
            }
        } else {
            echo "<p style='color:red'>Formato de imagem não permitido. Apenas JPG e PNG.</p>";
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, conteudo, imagem) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $conteudo, $imagem]);
}

// Deletar post
if (isset($_GET['delete_post'])) {
    $delete_post_id = intval($_GET['delete_post']);
    // Garante que só o dono pode deletar
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->execute([$delete_post_id, $user_id]);
    header('Location: feed.php');
    exit;
}

// Buscar posts
$stmt = $pdo->prepare("
    SELECT p.*, u.nome, u.avatar AS usuario_avatar
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY p.criado_em DESC
");
<<<<<<< HEAD
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
=======
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
?>
>>>>>>> 977d522ef25cc0e1f61cfcb3580e15b4b5527763

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Feed - ConectaTech</title>
<link rel="stylesheet" href="../public/assets/css/feed.css">
<style>
/* Reset e estilos básicos */
* { box-sizing: border-box; margin:0; padding:0; }
body { font-family: Arial,sans-serif; background:#f2f2f2; color:#333; line-height:1.6; }
header { background:#007bff; color:#fff; padding:12px 20px; display:flex; justify-content:space-between; align-items:center; }
header nav a { color:#fff; text-decoration:none; margin-left:20px; font-weight:bold; }
.container { width:90%; max-width:600px; margin:20px auto; }
.post-form { background:#fff; padding:16px; border-radius:12px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.post-form textarea { width:100%; height:80px; padding:10px; border-radius:5px; border:1px solid #ccc; }
.post-form button { margin-top:10px; padding:10px 20px; border:none; border-radius:6px; background:#007bff; color:#fff; cursor:pointer; }
.post { background:#fff; padding:15px; border-radius:12px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.08); }
.post-header { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.post-avatar { width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #007bff; }
.post .nome { font-weight:bold; font-size:1.1em; }
.post img.post-img { width:100%; border-radius:10px; margin-bottom:10px; }
.post-actions { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
.comment-section { margin-top:8px; display:none; }
.comment-form { display:flex; gap:8px; margin-bottom:8px; }
.comment-form input { flex:1; padding:6px 10px; border-radius:6px; border:1px solid #ccc; }
.comment-form button { padding:6px 12px; border-radius:6px; border:none; background:#007bff; color:#fff; cursor:pointer; }
.comments-list .comment-item { background:#f0f2f5; padding:6px 10px; border-radius:8px; margin-bottom:4px; font-size:0.9em; }
.post .data { font-size:0.75em; color:#777; text-align:right; }
.delete-post { margin-left:auto; background:red; color:#fff; border:none; padding:3px 8px; border-radius:4px; cursor:pointer; font-size:0.8em; }
</style>
</head>
<body>
<header>
<h1>ConectaTech</h1>
<nav>
<a href="profile.php">Meu Perfil</a>
<a href="chat.php">Chat</a>
<a href="logout.php">Sair</a>
</nav>
</header>

<div class="container">

<div class="post-form">
<form method="post" enctype="multipart/form-data">
<textarea name="conteudo" placeholder="O que você está pensando?" required></textarea><br>
<input type="file" name="imagem"><br>
<button type="submit">Postar</button>
</form>
</div>

<?php foreach($posts as $post): ?>
    <?php
        $stmtLikes = $pdo->prepare('SELECT COUNT(*) as likes FROM likes WHERE post_id=?');
        $stmtLikes->execute([$post['id']]);
        $likes = $stmtLikes->fetchColumn();

        $stmtComments = $pdo->prepare('SELECT c.*, u.nome FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.criado_em ASC');
        $stmtComments->execute([$post['id']]);
        $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="post" data-post-id="<?= $post['id'] ?>">
        <div class="post-header">
            <img src="<?= $post['usuario_avatar'] ? 'uploads/' . htmlspecialchars($post['usuario_avatar']) : 'assets/img/default-avatar.png' ?>" class="post-avatar" alt="Avatar de <?= htmlspecialchars($post['nome']) ?>">
            <div class="nome"><?= htmlspecialchars($post['nome']) ?></div>
            <?php if ($post['user_id'] == $user_id): ?>
                <a href="feed.php?delete_post=<?= $post['id'] ?>" class="delete-post" onclick="return confirm('Tem certeza que deseja excluir este post?')">Excluir</a>
            <?php endif; ?>
        </div>
        <div class="conteudo"><?= nl2br(htmlspecialchars($post['conteudo'])) ?></div>
        <?php if ($post['imagem']): ?>
            <img src="<?= htmlspecialchars($post['imagem']) ?>" class="post-img" alt="Imagem do post">
        <?php endif; ?>
        <div class="data"><?= $post['criado_em'] ?></div>
        <div class="post-actions">
            <button class="like-btn" data-post-id="<?= $post['id'] ?>">Curtir</button>
            <span class="like-count" id="like-count-<?= $post['id'] ?>"><?= $likes ?></span>
            <button class="comment-toggle-btn" data-post-id="<?= $post['id'] ?>">Comentar</button>
        </div>
        <div class="comment-section" id="comment-section-<?= $post['id'] ?>">
            <form class="comment-form" data-post-id="<?= $post['id'] ?>">
                <input type="text" name="comentario" placeholder="Digite seu comentário..." required>
                <button type="submit">Enviar</button>
            </form>
            <div class="comments-list" id="comments-list-<?= $post['id'] ?>">
                <?php foreach($comments as $c): ?>
                    <div class="comment-item"><strong><?= htmlspecialchars($c['nome']) ?></strong>: <?= htmlspecialchars($c['conteudo']) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script src="../public/assets/js/app.js"></script>
<script>
// Toggle comentários
document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.dataset.postId;
        const section = document.getElementById('comment-section-' + postId);
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });
});

// AJAX comentários
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const postId = this.dataset.postId;
        const conteudo = this.querySelector('input[name="comentario"]').value;
        fetch('../ajax/comment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'post_id=' + postId + '&conteudo=' + encodeURIComponent(conteudo)
        })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'ok' && data.comment){
                const list = document.getElementById('comments-list-' + postId);
                const div = document.createElement('div');
                div.className = 'comment-item';
                div.innerHTML = `<strong>${data.comment.nome}</strong>: ${data.comment.conteudo}`;
                list.appendChild(div);
                this.querySelector('input[name="comentario"]').value = '';
            }else{
                alert('Erro ao comentar');
            }
        });
    });
});

// AJAX likes
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.dataset.postId;
        fetch('../ajax/like.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'post_id=' + postId
        })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'liked' || data.status === 'unliked'){
                fetch('../ajax/like.php?count=1&post_id=' + postId)
                  .then(r => r.json())
                  .then(countData => {
                    if(countData.likes !== undefined){
                      document.getElementById('like-count-' + postId).textContent = countData.likes;
                    }
                  });
            }else{
                alert('Erro ao curtir');
            }
        });
    });
});
</script>
</body>
</html>
