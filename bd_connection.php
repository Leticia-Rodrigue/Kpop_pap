<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/app_settings.php';

$appSettings = load_app_settings();
$host = app_setting($appSettings, 'db.host');
$user = app_setting($appSettings, 'db.user');
$pass = app_setting($appSettings, 'db.pass', '');
$dbname = app_setting($appSettings, 'db.name');

if (!$host || !$user || !$dbname) {
    error_log('Database configuration is incomplete.');
    http_response_code(500);
    die('Erro interno de configuracao.');
}

// Ligação mysqli (usada na maioria das páginas)
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    error_log('MySQL connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    die('Erro interno de ligacao.');
}
mysqli_set_charset($conn, "utf8mb4");

// Ligação PDO (usada em textos.php)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
    error_log("PDO connection failed: " . $e->getMessage());
}
?>