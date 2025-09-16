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
        // quando o login for bem-sucedido
        $_SESSION['user_id']=$u['id'];
        header('Location: ' . BASE_URL . '/public/feed.php');
        exit;
    } else { $error='Credenciais inválidas.'; }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <h1>Entrar</h1>
  <?php if(!empty($error)): ?><div class="alert"><?=esc($error)?></div><?php endif; ?>
  <form method="post">
    <label>Email <input name="email" type="email" required></label>
    <label>Senha <input name="senha" type="password" required></label>
    <button type="submit">Entrar</button>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
