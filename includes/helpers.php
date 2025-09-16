<?php
function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function uploadImage($file, $destDir = __DIR__ . '/../public/uploads/') {
    if(!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['image/jpeg','image/png','image/gif'];
    if(!in_array($file['type'], $allowed)) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if(!move_uploaded_file($file['tmp_name'], $destDir . $name)) return null;
    return $name;
}
