<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Você precisa estar logado para acessar o feed.");
}

// Criar post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['conteudo']) && !isset($_POST['comentario'])) {
    $conteudo = $_POST['conteudo'];
    $imagem = '';

    if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['imagem']['type'], $allowedTypes)) {
            $uploadDir = __DIR__ . '/../public/uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $imagemNome = uniqid('img_') . '.' . $ext;
            $imagemPath = $uploadDir . $imagemNome;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemPath)) {
                $imagem = 'uploads/' . $imagemNome;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, conteudo, imagem) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $conteudo, $imagem]);
}

// Deletar post
if (isset($_GET['delete_post'])) {
    $delete_post_id = intval($_GET['delete_post']);
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
    $stmt->execute([$delete_post_id, $user_id]);
    header('Location: feed.php');
    exit;
}

// Função AJAX: Curtir
if (isset($_POST['action']) && $_POST['action'] === 'like') {
    $postId = intval($_POST['post_id']);
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id=? AND user_id=?");
    $stmt->execute([$postId, $user_id]);

    if ($stmt->fetch()) {
        $pdo->prepare("DELETE FROM likes WHERE post_id=? AND user_id=?")->execute([$postId, $user_id]);
    } else {
        $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$postId, $user_id]);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id=?");
    $stmt->execute([$postId]);
    echo $stmt->fetchColumn();
    exit;
}

// Função AJAX: Comentar
if (isset($_POST['action']) && $_POST['action'] === 'comment') {
    $postId = intval($_POST['post_id']);
    $comentario = trim($_POST['comentario']);
    if ($comentario !== '') {
        $pdo->prepare("INSERT INTO comments (post_id, user_id, conteudo) VALUES (?, ?, ?)")
            ->execute([$postId, $user_id, $comentario]);

        $stmt = $pdo->prepare("SELECT u.nome, u.avatar, c.conteudo 
                               FROM comments c 
                               JOIN users u ON c.user_id=u.id 
                               WHERE c.post_id=? 
                               ORDER BY c.id DESC LIMIT 1");
        $stmt->execute([$postId]);
        $newComment = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'nome' => htmlspecialchars($newComment['nome']),
            'avatar' => $newComment['avatar'] ? 'uploads/' . $newComment['avatar'] : 'assets/img/default-avatar.png',
            'conteudo' => htmlspecialchars($newComment['conteudo'])
        ]);
    }
    exit;
}

// Buscar posts
$stmt = $pdo->prepare("
    SELECT p.*, u.nome, u.avatar AS usuario_avatar
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.criado_em DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Feed - ConectaTech</title>
<link rel="stylesheet" href="../public/assets/css/feed.css">
<style>
/* ... mesmo CSS que você já tem ... */
.comment-item { display:flex; align-items:center; gap:8px; }
.comment-avatar { width:30px; height:30px; border-radius:50%; object-fit:cover; }
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

        $stmtUserLiked = $pdo->prepare('SELECT 1 FROM likes WHERE post_id=? AND user_id=?');
        $stmtUserLiked->execute([$post['id'], $user_id]);
        $userLiked = $stmtUserLiked->fetch();

        $stmtComments = $pdo->prepare('SELECT c.*, u.nome, u.avatar 
                                       FROM comments c 
                                       JOIN users u ON u.id=c.user_id 
                                       WHERE c.post_id=? ORDER BY c.criado_em ASC');
        $stmtComments->execute([$post['id']]);
        $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="post" data-post-id="<?= $post['id'] ?>">
        <div class="post-header">
            <img src="<?= $post['usuario_avatar'] ? '../public/uploads/' . htmlspecialchars($post['usuario_avatar']) : '../public/assets/img/default-avatar.png' ?>" class="post-avatar" alt="Avatar de <?= htmlspecialchars($post['nome']) ?>">
            <div class="nome"><?= htmlspecialchars($post['nome']) ?></div>
            <?php if ($post['user_id'] == $user_id): ?>
                <a href="feed.php?delete_post=<?= $post['id'] ?>" class="delete-post" onclick="return confirm('Tem certeza que deseja excluir este post?')">Excluir</a>
            <?php endif; ?>
        </div>
        <div class="conteudo"><?= nl2br(htmlspecialchars($post['conteudo'])) ?></div>
        <?php if ($post['imagem']): ?>
            <img src="<?= '../public/' . htmlspecialchars($post['imagem']) ?>" class="post-img" alt="Imagem do post">
        <?php endif; ?>
        <div class="data"><?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?></div>
        <div class="post-actions">
            <button class="like-btn" data-post-id="<?= $post['id'] ?>"><?= $userLiked ? 'Descurtir' : 'Curtir' ?></button>
            <span class="like-count" id="like-count-<?= $post['id'] ?>"><?= $likes ?></span>
            <button class="comment-toggle-btn" data-post-id="<?= $post['id'] ?>">Comentar</button>
        </div>
        <div class="comment-section" id="comment-section-<?= $post['id'] ?>" style="display:none">
            <form class="comment-form" data-post-id="<?= $post['id'] ?>">
                <input type="text" name="comentario" placeholder="Digite seu comentário..." required>
                <button type="submit">Enviar</button>
            </form>
            <div class="comments-list" id="comments-list-<?= $post['id'] ?>">
                <?php foreach($comments as $c): ?>
                    <div class="comment-item">
                        <img src="<?= $c['avatar'] ? '../public/uploads/' . htmlspecialchars($c['avatar']) : '../public/assets/img/default-avatar.png' ?>" class="comment-avatar">
                        <div><strong><?= htmlspecialchars($c['nome']) ?></strong>: <?= htmlspecialchars($c['conteudo']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>

</div>

<script>
// Curtir
document.querySelectorAll('.like-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const postId = this.dataset.postId;
        fetch('feed.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/x-www-form-urlencoded' },
            body: 'action=like&post_id=' + postId
        })
        .then(res => res.text())
        .then(count => {
            document.getElementById('like-count-' + postId).textContent = count;
            this.textContent = this.textContent === 'Curtir' ? 'Descurtir' : 'Curtir';
        });
    });
});

// Mostrar/ocultar comentários
document.querySelectorAll('.comment-toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const postId = this.dataset.postId;
        const section = document.getElementById('comment-section-' + postId);
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    });
});

// Enviar comentário
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const postId = this.dataset.postId;
        const input = this.querySelector('input[name="comentario"]');
        fetch('feed.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/x-www-form-urlencoded' },
            body: 'action=comment&post_id=' + postId + '&comentario=' + encodeURIComponent(input.value)
        })
        .then(res => res.json())
        .then(data => {
            const list = document.getElementById('comments-list-' + postId);
            const div = document.createElement('div');
            div.classList.add('comment-item');
            div.innerHTML = `<img src="../public/${data.avatar}" class="comment-avatar"> <div><strong>${data.nome}</strong>: ${data.conteudo}</div>`;
            list.appendChild(div);
            input.value = '';
        });
    });
});
</script>
</body>
</html>
