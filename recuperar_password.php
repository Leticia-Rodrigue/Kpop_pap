<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'bd_connection.php';

$action = $_POST['action'] ?? '';

if ($action === 'verificar_email') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $res = mysqli_query($conn, "SELECT id_utilizador FROM utilizadores WHERE email = '$email'");
    
    if (mysqli_num_rows($res) > 0) {
        $_SESSION['recovery_email'] = $email;
        echo "success";
    } else {
        echo "Email não encontrado.";
    }
}

if ($action === 'nova_password') {
    if (!isset($_SESSION['recovery_email'])) {
        echo "Sessão expirada.";
        exit;
    }
    $nova = $_POST['nova'];
    $conf = $_POST['conf'];
    
    if (strlen($nova) < 6) {
        echo "A password deve ter pelo menos 6 caracteres.";
    } elseif ($nova !== $conf) {
        echo "As passwords não coincidem.";
    } else {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $email = $_SESSION['recovery_email'];
        if (mysqli_query($conn, "UPDATE utilizadores SET senha_hash='$hash' WHERE email='$email'")) {
            unset($_SESSION['recovery_email']);
            echo "success";
        } else {
            echo "Erro ao atualizar BD.";
        }
    }
}
?>