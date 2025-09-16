<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

// Inicializa variáveis para evitar warnings
$u = null;
$mensagem = '';

// Pega ID do usuário ou do perfil atual
$id = intval($_GET['id'] ?? currentUserId());

// Buscar usuário
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
    die("Usuário não encontrado");
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!$nome || !$email) {
        $mensagem = "Nome e email são obrigatórios.";
    } else {
        // Atualiza foto se houver upload
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($ext, $permitidos)) {
                $novoNome = 'user_' . $id . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/';
                $destino  = $uploadDir . $novoNome;

                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $stmt = $pdo->prepare('UPDATE users SET avatar=? WHERE id=?');
                    $stmt->execute([$novoNome, $id]);
                    $u['avatar'] = $novoNome; // Atualiza avatar na tela
                } else {
                    $mensagem = "Erro ao enviar a foto.";
                }
            } else {
                $mensagem = "Formato de imagem inválido. Use jpg, png ou gif.";
            }
        }

        // Atualiza nome e email
        $stmt = $pdo->prepare('UPDATE users SET nome=?, email=? WHERE id=?');
        $stmt->execute([$nome, $email, $id]);

        $u['nome']  = $nome;
        $u['email'] = $email;

        if (!$mensagem) {
            $mensagem = "Perfil atualizado com sucesso!";
        }
    }
}

// Foto do usuário ou default
$foto = $u['avatar'] ?? 'default.jpg';
$imagemCaminho = file_exists(__DIR__ . '/../uploads/' . $foto) 
    ? BASE_URL . '/uploads/' . $foto 
    : BASE_URL . '/uploads/default.jpg';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - <?= htmlspecialchars($u['nome'] ?? 'Usuário') ?></title>
    <style>
        body { font-family: Arial, sans-serif; background:#f5f7fa; display:flex; justify-content:center; padding:40px 20px; }
        .perfil { background:#fff; padding:30px; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1); width:100%; max-width:450px; text-align:center; }
        .perfil img { width:150px; height:150px; border-radius:50%; object-fit:cover; border:3px solid #007bff; margin-bottom:20px; }
        h1 { font-size:1.6rem; margin-bottom:25px; color:#333; }
        form { text-align:left; }
        label { display:block; font-weight:bold; margin-bottom:6px; color:#555; }
        input[type=text], input[type=email], input[type=file] { width:100%; padding:12px; margin-bottom:20px; border-radius:8px; border:1px solid #ccc; font-size:1rem; }
        input[type=submit] { width:100%; padding:12px; border:none; border-radius:8px; background:#007bff; color:#fff; font-size:1.1rem; font-weight:bold; cursor:pointer; }
        .mensagem { margin-bottom:20px; padding:10px 15px; background:#d4edda; color:#155724; border-radius:8px; border:1px solid #c3e6cb; text-align:center; font-weight:bold; }
    </style>
</head>
<body>
    <div class="perfil">
        <h1>Editar Perfil de <?= htmlspecialchars($u['nome'] ?? 'Usuário') ?></h1>
        <?php if($mensagem): ?>
            <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($imagemCaminho) ?>" alt="Foto de <?= htmlspecialchars($u['nome'] ?? 'Usuário') ?>">
        <form action="" method="post" enctype="multipart/form-data">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($u['nome'] ?? '') ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($u['email'] ?? '') ?>" required>

            <label>Foto de Perfil:</label>
            <input type="file" name="foto" accept="image/*">

            <input type="submit" value="Atualizar Perfil">
        </form>
    </div>
</body>
</html>
