<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$pageTitle='Cadastrar - Rede Social';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    if(!$nome || !$email || !$senha) $error='Preencha todos os campos.';
    else{
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $stmt->execute([$email]);
        if($stmt->fetch()) $error='Email já cadastrado.';
        else{
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (nome,email,senha) VALUES (?,?,?)');
            $stmt->execute([$nome,$email,$hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: ' . BASE_URL . '/public/feed.php'); exit;

        }
    }
}
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <h1>Cadastrar</h1>
  <?php if(!empty($error)): ?><div class="alert"><?=esc($error)?></div><?php endif; ?>
  <form method="post">
    <label>Nome <input name="nome" required></label>
    <label>Email <input name="email" type="email" required></label>
    <label>Senha <input name="senha" type="password" required></label>
    <button type="submit">Cadastrar</button>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
