<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "k-popuniverse";

// Ligação mysqli (usada na maioria das páginas)
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    die("Falha na ligação: " . mysqli_connect_error());
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