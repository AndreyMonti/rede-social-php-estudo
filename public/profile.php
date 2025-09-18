<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Buscar usuário
$id = intval($_GET['id'] ?? currentUserId());
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) { 
    echo 'Usuário não encontrado'; 
    exit; 
}

$pageTitle = 'Perfil - ' . $u['nome'];
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';

// Número de posts
$stmtPosts = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE user_id=?');
$stmtPosts->execute([$id]);
$numPosts = $stmtPosts->fetchColumn();

// Número de likes
$stmtLikes = $pdo->prepare('SELECT COUNT(*) 
                            FROM likes l 
                            JOIN posts p ON l.post_id = p.id 
                            WHERE p.user_id=?');
$stmtLikes->execute([$id]);
$numLikes = $stmtLikes->fetchColumn();

// Buscar posts do usuário
$stmtUserPosts = $pdo->prepare('SELECT * FROM posts WHERE user_id=? ORDER BY criado_em DESC');
$stmtUserPosts->execute([$id]);
$userPosts = $stmtUserPosts->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/profile.css">

<div class="profile-header">
    <div class="profile-avatar">
        <img src="<?= $u['avatar'] 
                      ? BASE_URL . '/public/uploads/avatars/' . esc($u['avatar']) 
                      : BASE_URL . '/public/assets/img/default-avatar.png' ?>" 
             alt="Avatar de <?= esc($u['nome']) ?>">
    </div>
    <div class="profile-info">
        <h1><?= esc($u['nome']) ?></h1>
        <?php if($u['bio']): ?>
            <p class="bio"><?= nl2br(esc($u['bio'])) ?></p>
        <?php endif; ?>
        <div class="profile-stats">
            <div><strong><?= $numPosts ?></strong><br>Posts</div>
            <div><strong><?= $numLikes ?></strong><br>Likes</div>
            <div><strong><?= date('m/Y', strtotime($u['criado_em'])) ?></strong><br>Desde</div>
        </div>

        <?php if(currentUserId() === $u['id']): ?>
            <div class="profile-actions">
                <a href="<?= BASE_URL ?>/public/edit_profile.php" class="btn-edit">Editar Perfil</a>
                <a href="<?= BASE_URL ?>/public/delete_account.php" class="btn-delete" 
                   onclick="return confirm('Tem certeza que deseja excluir sua conta? Essa ação não pode ser desfeita.');">
                   Excluir Conta
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="profile-posts">
    <?php if(count($userPosts) === 0): ?>
        <p class="no-posts">Nenhuma postagem encontrada.</p>
    <?php else: ?>
        <?php foreach($userPosts as $post): ?>
            <div class="post-card">
                <div class="post-header">
                    <img src="<?= $u['avatar'] 
                                  ? BASE_URL . '/public/uploads/avatars/' . esc($u['avatar']) 
                                  : BASE_URL . '/public/assets/img/default-avatar.png' ?>" 
                         alt="Avatar de <?= esc($u['nome']) ?>">
                    <strong><?= esc($u['nome']) ?></strong>
                </div>
                <div class="post-content"><?= nl2br(esc($post['conteudo'])) ?></div>
                <?php if($post['imagem']): ?>
                    <div class="post-img-wrap">
                        <img src="<?= BASE_URL . '/public/uploads/posts' . esc($post['imagem']) ?>" class="post-img" alt="Imagem do post">
                    </div>
                <?php endif; ?>
                <div class="post-footer">
                    <span class="post-date"><?= date('d/m/Y H:i', strtotime($post['criado_em'])) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
