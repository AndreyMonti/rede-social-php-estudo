<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = intval($_GET['id'] ?? currentUserId());
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    echo "Usuário não encontrado";
    exit;
}

// Mensagem de feedback
$mensagem = '';

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

                // Cria a pasta caso não exista
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $stmt = $pdo->prepare('UPDATE users SET avatar=? WHERE id=?');
                    $stmt->execute([$novoNome, $id]);
                    $u['avatar'] = $novoNome; // já atualiza na tela
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
$imagemCaminho = BASE_URL . '/uploads/' . $foto;

// Garante que exista default.jpg
if (!file_exists(__DIR__ . '/../uploads/' . $foto)) {
    $imagemCaminho = BASE_URL . '/uploads/default.jpg';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil - <?= htmlspecialchars($u['nome']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .perfil { max-width: 400px; margin: auto; text-align: center; }
        .perfil img { max-width: 200px; height: auto; border-radius: 8px; margin-bottom: 20px; }
        form { text-align: left; }
        input[type=text], input[type=email] { width: 100%; padding: 8px; margin-bottom: 10px; }
        input[type=file] { margin-bottom: 10px; }
        input[type=submit] { padding: 10px 20px; cursor: pointer; }
        .mensagem { margin-bottom: 20px; color: green; }
    </style>
</head>
<body>
    <div class="perfil">
        <h1>Editar Perfil de <?= htmlspecialchars($u['nome']) ?></h1>
        <?php if($mensagem): ?>
            <div class="mensagem"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($imagemCaminho) ?>" alt="Foto de <?= htmlspecialchars($u['nome']) ?>">
        <form action="" method="post" enctype="multipart/form-data">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($u['nome']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>

            <label>Foto de Perfil:</label>
            <input type="file" name="foto" accept="image/*">

            <input type="submit" value="Atualizar Perfil">
        </form>
    </div>
</body>
</html>
