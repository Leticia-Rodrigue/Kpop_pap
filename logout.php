<?php
// 1. Inicia a sessão para ter acesso aos dados atuais
session_start();

// 2. Remove todas as variáveis de sessão (username, role, etc.)
$_SESSION = array();

// 3. Se desejar destruir completamente a sessão, apague também o cookie da sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destrói a sessão no servidor
session_destroy();

// 5. Redireciona o utilizador para a página inicial (index.php)
header("Location: index.php");
exit();
?>