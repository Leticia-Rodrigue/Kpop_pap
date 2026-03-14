<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$chars = '0123456789';
$code  = '';
for ($i = 0; $i < 6; $i++) {
    $code .= $chars[random_int(0, 9)];
}

$_SESSION['captcha_code'] = $code;

header('Content-Type: application/json');
echo json_encode(['codigo' => $code]);
?>