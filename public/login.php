<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle='Login - Rede Social';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if($u && password_verify($senha, $u['senha'])){
        $_SESSION['user_id']=$u['id'];
        header('Location: ' . BASE_URL . '/public/feed.php');
        exit;
    } else { $error='Credenciais inválidas.'; }
}
include __DIR__ . '/../includes/head.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>./public/assets/css/login.css">
<div class="container">
    <h1>Entrar</h1>
    <?php if(!empty($error)): ?><div class="alert"><?=esc($error)?></div><?php endif; ?>
    <form method="post">
        <label>Email <input name="email" type="email" required></label>
        <label>Senha <input name="senha" type="password" required></label>
        <button type="submit" class="button button-primary">Entrar</button>
    </form>
    
    <div class="button-group">
        <a href="<?= BASE_URL ?>/public/register.php" class="button button-secondary">
            Criar conta
        </a>
    </div>
</div>
