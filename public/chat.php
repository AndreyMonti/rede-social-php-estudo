<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';
if(!isLogged()) header('Location: /public/login.php');
$pageTitle='Chat';
include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/header.php';
?>
<div class="container">
  <h2>Mensagens</h2>
  <p>Chat básico (use ajax/send_message.php e ajax/fetch_messages.php)</p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
