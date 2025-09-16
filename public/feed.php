<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if(!$user_id){
    die("Você precisa estar logado para acessar o feed.");
}

// Criar post
if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['conteudo'])){
    $conteudo = $_POST['conteudo'];
    $imagem = '';

    // Upload de imagem
    if(isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0){
        $uploadDir = __DIR__ . '/uploads/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0777, true);
        }
        $imagemNome = basename($_FILES['imagem']['name']);
        $imagem = 'uploads/' . $imagemNome;
        move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadDir . $imagemNome);
    }

    // Salvar post no banco
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, conteudo, imagem) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $conteudo, $imagem]);
}

// Buscar posts do usuário
$stmt = $pdo->prepare("
    SELECT p.*, u.nome, u.avatar AS usuario_avatar
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.criado_em DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
    <link rel="stylesheet" href="../public/assets/css/feed.css">
<head>
    <meta charset="UTF-8">
        
</head>
<body>
    <header>
        <h1>ConectaTech</h1>
        <nav>
            <a href="feed.php">Feed</a>
            <a href="profile.php">Perfil</a>
            <a href="logout.php">Sair</a>
        </nav>
    </header>
    <div class="container">
        <h2>Feed ConectaTech</h2>

        <div class="post-form">
            <form method="post" enctype="multipart/form-data">
                <textarea name="conteudo" placeholder="O que você está pensando?" required></textarea><br>
                <input type="file" name="imagem"><br>
                <button type="submit">Postar</button>
            </form>
        </div>

        <?php foreach($posts as $post): ?>
            <?php
                // Buscar número de likes
                $stmtLikes = $pdo->prepare('SELECT COUNT(*) as likes FROM likes WHERE post_id=?');
                $stmtLikes->execute([$post['id']]);
                $likes = $stmtLikes->fetchColumn();

                // Buscar comentários
                $stmtComments = $pdo->prepare('SELECT c.*, u.nome FROM comments c JOIN users u ON u.id=c.user_id WHERE c.post_id=? ORDER BY c.criado_em ASC');
                $stmtComments->execute([$post['id']]);
                $comments = $stmtComments->fetchAll();
            ?>
            <div class="post" data-post-id="<?= $post['id'] ?>">
                <div class="post-header">
    <img 
        src="<?= $post['usuario_avatar'] ? '/public/uploads/avatar/' . htmlspecialchars($post['usuario_avatar']) : '/public/assets/img/default-avatar.png' ?>" 
        alt="Avatar de <?= htmlspecialchars($post['nome']) ?>" 
        class="post-avatar"
    >
    <span class="nome"><?= htmlspecialchars($post['nome']) ?></span>
</div>
                <div class="conteudo"><?= nl2br(htmlspecialchars($post['conteudo'])) ?></div>
                <?php if($post['imagem']): ?>
                    <img src="<?= htmlspecialchars($post['imagem']) ?>" alt="Imagem do post">
                <?php endif; ?>
                <div class="data"><?= $post['criado_em'] ?></div>
                <div class="post-actions">
                    <button class="like-btn" data-post-id="<?= $post['id'] ?>">Curtir</button>
                    <span class="like-count" id="like-count-<?= $post['id'] ?>"><?= $likes ?></span>
                    <button class="comment-toggle-btn" data-post-id="<?= $post['id'] ?>">Comentar</button>
                </div>
                <div class="comment-section" id="comment-section-<?= $post['id'] ?>" style="display:none;">
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
// Exibe/esconde área de comentários
document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.dataset.postId;
        const section = document.getElementById('comment-section-' + postId);
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });
});

// Envia comentário via AJAX
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

// Curtir post via AJAX
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
                // Atualiza contador de likes (opcional: buscar do backend)
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
