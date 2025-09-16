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

// Processar envio do formulário
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
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
                $destino = __DIR__ . '/../uploads/' . $novoNome;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $stmt = $pdo->prepare('UPDATE users SET foto=? WHERE id=?');
                    $stmt->execute([$novoNome, $id]);
                } else {
                    $mensagem = "Erro ao enviar a foto.";
                }
            } else {
                $mensagem = "Formato de imagem inválido.";
            }
        }

        // Atualiza nome e email
        $stmt = $pdo->prepare('UPDATE users SET nome=?, email=? WHERE id=?');
        $stmt->execute([$nome, $email, $id]);
        $mensagem = "Perfil atualizado com sucesso!";
        // Atualiza $u para mostrar alterações imediatamente
        $u['nome'] = $nome;
        $u['email'] = $email;
    }
}

// Caminho da imagem
$imagemCaminho = __DIR__ . '/../uploads/' . ($u['foto'] ?? 'default.jpg');
if (!file_exists($imagemCaminho)) {
    $imagemCaminho = __DIR__ . '/../uploads/default.jpg';
}

function urlImagem($caminho) {
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath($caminho));
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
        <img src="<?= urlImagem($imagemCaminho) ?>" alt="Foto de <?= htmlspecialchars($u['nome']) ?>">
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
