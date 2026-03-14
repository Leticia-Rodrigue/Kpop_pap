<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/bd_connection.php';
require_once __DIR__ . '/password_reset_helper.php';

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$statusMessage = '';
$statusType = 'error';
$tokenRecord = $token !== '' ? get_password_reset_token_record($conn, $token) : null;
$isTokenValid = is_password_reset_token_valid($tokenRecord);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_reset_password'])) {
    $nova = trim($_POST['nova_password'] ?? '');
    $conf = trim($_POST['conf_password'] ?? '');

    if (!$isTokenValid) {
        $statusMessage = 'Este link expirou ou já foi usado.';
    } elseif (strlen($nova) < 6) {
        $statusMessage = 'A password deve ter pelo menos 6 caracteres.';
    } elseif ($nova !== $conf) {
        $statusMessage = 'As passwords não coincidem.';
    } elseif (reset_password_with_token($conn, $token, $nova)) {
        $statusType = 'success';
        $statusMessage = 'Password atualizada com sucesso. Já podes iniciar sessão.';
        $isTokenValid = false;
    } else {
        $statusMessage = 'Não foi possível atualizar a password. Pede um novo link.';
    }
}

if ($token === '' && $statusMessage === '') {
    $statusMessage = 'Link de recuperação em falta ou inválido.';
}

if ($token !== '' && !$isTokenValid && $statusMessage === '') {
    $statusMessage = 'Este link expirou ou já foi usado.';
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Recuperar Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #111827, #050505 60%);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            padding: 24px;
            box-sizing: border-box;
        }
        .reset-card {
            width: 100%;
            max-width: 460px;
            background: rgba(13, 13, 13, 0.96);
            border: 1px solid rgba(0, 212, 255, 0.25);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 0 40px rgba(0, 212, 255, 0.12);
        }
        h1 {
            margin: 0 0 10px;
            font-size: 1.6rem;
        }
        p {
            color: #8b8b8b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .status {
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.85rem;
            margin-bottom: 18px;
        }
        .status.error {
            background: rgba(255, 68, 102, 0.08);
            border: 1px solid rgba(255, 68, 102, 0.22);
            color: #ff6b88;
        }
        .status.success {
            background: rgba(0, 212, 100, 0.08);
            border: 1px solid rgba(0, 212, 100, 0.2);
            color: #00d464;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            box-sizing: border-box;
            outline: none;
        }
        input:focus {
            border-color: rgba(0, 212, 255, 0.5);
        }
        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 14px 18px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(45deg, #00c9ff, #00d4ff);
            color: #000;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary {
            margin-top: 12px;
            background: transparent;
            border: 1px solid rgba(0, 212, 255, 0.25);
            color: #00d4ff;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <h1>Definir Nova Password</h1>
        <p>Usa este formulário para escolher uma nova password segura para a tua conta.</p>

        <?php if ($statusMessage !== ''): ?>
            <div class="status <?php echo $statusType; ?>"><?php echo htmlspecialchars($statusMessage); ?></div>
        <?php endif; ?>

        <?php if ($isTokenValid): ?>
            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" name="nova_password" placeholder="Nova password" required>
                <input type="password" name="conf_password" placeholder="Confirmar password" required>
                <button type="submit" name="btn_reset_password" class="btn">Guardar Password</button>
            </form>
        <?php endif; ?>

        <a href="registar.php" class="btn btn-secondary">Voltar ao Login</a>
    </div>
</body>
</html>