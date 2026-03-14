<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'bd_connection.php';
require_once __DIR__ . '/password_reset_helper.php';

// --- RECUPERAR PASSWORD: pedir email e enviar link ---
if (isset($_POST['btn_request_password_reset'])) {
    $recovery_step = 1;
    $recovery_email = trim($_POST['recovery_email'] ?? '');

    if ($recovery_email === '' || !filter_var($recovery_email, FILTER_VALIDATE_EMAIL)) {
        $recovery_error = 'Introduz um email válido.';
    } else {
        if (request_password_reset($conn, $recovery_email)) {
            $recovery_step = 2;
        } else {
            $recovery_error = 'Não foi possível processar o pedido agora. Tenta novamente.';
        }
    }
}

// --- REGISTO ---
if (isset($_POST['btn_registar'])) {
    $nome  = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $res_check = mysqli_query($conn, "SELECT id_utilizador FROM utilizadores WHERE email = '$email'");
    if (mysqli_num_rows($res_check) > 0) {
        echo "<script>alert('Este email já está registado!');</script>";
    } else {
        $sql = "INSERT INTO utilizadores (nome, email, senha_hash, role_id, ativo) VALUES ('$nome', '$email', '$pass', 3, 1)";
        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('Conta criada com sucesso! Faça login para entrar.');</script>";
        } else {
            echo "Erro ao registar: " . mysqli_error($conn);
        }
    }
}

// --- LOGIN ---
if (isset($_POST['btn_login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['password'];
    $res   = mysqli_query($conn, "SELECT * FROM utilizadores WHERE email = '$email'");
    $user  = mysqli_fetch_assoc($res);
    if ($user && password_verify($pass, $user['senha_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id_utilizador'];
        $_SESSION['username'] = $user['nome'];
        $_SESSION['role']     = $user['role_id'];
        switch ($_SESSION['role']) {
            case 1: header("Location: painel_adminMaster.php"); break;
            case 2: header("Location: admin_painel.php"); break;
            default: header("Location: index.php"); break;
        }
        exit();
    } else {
        echo "<script>alert('Email ou Password incorretos!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Login</title>
    <link rel="stylesheet" href="css/style_registar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow-x: hidden; }
        .video-background-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; background: #000; }
        .video-background-container::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1; }
        .video-wrapper { position: absolute; width: 100%; height: 100%; display: none; pointer-events: none; }
        .video-wrapper.active { display: block; }
        .video-wrapper iframe { width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.77vh; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }
        .main-wrapper { position: relative; z-index: 10; display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; background: rgba(0,0,0,0.7); backdrop-filter: blur(6px); justify-content: center; align-items: center; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: #0d0d0d; border: 1px solid rgba(0,212,255,0.3); border-radius: 20px; padding: 40px; width: 100%; max-width: 420px; box-shadow: 0 0 40px rgba(0,212,255,0.15); animation: modalIn 0.35s cubic-bezier(0.175,0.885,0.32,1.275); position: relative; }
        @keyframes modalIn { from { opacity: 0; transform: scale(0.85) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        .modal-step { display: none; }
        .modal-step.active { display: block; }
        .modal-box h2 { font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0 0 8px 0; letter-spacing: 1px; }
        .modal-box p { font-size: 0.85rem; color: #666; margin-bottom: 24px; line-height: 1.5; }
        .modal-box input { width: 100%; padding: 12px 16px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04); color: #fff; font-family: "Poppins",sans-serif; font-size: 0.9rem; margin-bottom: 12px; box-sizing: border-box; transition: border-color 0.3s; outline: none; }
        .modal-box input:focus { border-color: rgba(0,212,255,0.5); }
        .modal-error { color: #ff4466; font-size: 0.78rem; margin-bottom: 12px; min-height: 18px; font-weight: 600; }
        .modal-btn { width: 100%; padding: 13px; border-radius: 10px; border: none; background: linear-gradient(45deg,#00c9ff,#00d4ff); color: #000; font-weight: 800; font-size: 0.9rem; letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: all 0.3s; margin-top: 4px; }
        .modal-btn:hover { box-shadow: 0 0 20px rgba(0,212,255,0.5); transform: translateY(-2px); }
        .modal-close { position: absolute; top: 14px; right: 18px; background: none; border: none; color: #555; font-size: 1.3rem; cursor: pointer; transition: color 0.2s; }
        .modal-close:hover { color: #fff; }
        .modal-btn-ghost { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid rgba(0,212,255,0.3); background: transparent; color: #00d4ff; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s; margin-top: 8px; }
        .modal-btn-ghost:hover { background: rgba(0,212,255,0.1); }
        .success-icon { font-size: 3rem; text-align: center; margin-bottom: 16px; display: block; }
        .center { text-align: center; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="video-background-container">
    <div class="video-wrapper active"><iframe src="https://www.youtube.com/embed/P169hsXjYQs?autoplay=1&mute=1&loop=1&playlist=P169hsXjYQs&controls=0&showinfo=0&rel=0" frameborder="0"></iframe></div>
    <div class="video-wrapper"><iframe src="https://www.youtube.com/embed/gdZLi9oWNZg?autoplay=1&mute=1&loop=1&playlist=gdZLi9oWNZg&controls=0&showinfo=0&rel=0" frameborder="0"></iframe></div>
    <div class="video-wrapper"><iframe src="https://www.youtube.com/embed/gQlMMD8auMs?autoplay=1&mute=1&loop=1&playlist=gQlMMD8auMs&controls=0&showinfo=0&rel=0" frameborder="0"></iframe></div>
    <div class="video-wrapper"><iframe src="https://www.youtube.com/embed/e3YTbgramxo?autoplay=1&mute=1&loop=1&playlist=e3YTbgramxo&controls=0&showinfo=0&rel=0" frameborder="0"></iframe></div>
</div>

<!-- MODAL RECUPERAR PASSWORD -->
<div class="modal-overlay" id="recoveryModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeRecovery()">&#x2715;</button>

        <!-- Passo 1: email -->
        <div class="modal-step active" id="step1">
            <h2>Recuperar Password</h2>
            <p>Introduz o email da tua conta. Se existir, enviamos um link seguro para redefinires a password.</p>
            <form method="POST" action="">
                <input type="email" name="recovery_email" placeholder="O teu email" value="<?php echo htmlspecialchars($recovery_email ?? ''); ?>" required>
                <div class="modal-error" id="recovery_error"></div>
                <button type="submit" name="btn_request_password_reset" class="modal-btn">Enviar Link</button>
            </form>
            <button class="modal-btn-ghost" onclick="closeRecovery()">Cancelar</button>
        </div>

        <!-- Passo 2: confirmação -->
        <div class="modal-step" id="step2">
            <span class="success-icon">&#x2709;</span>
            <h2 class="center">Verifica o teu email</h2>
            <p class="center">Se existir uma conta com esse email, enviámos um link único para redefinires a password.</p>
            <button class="modal-btn" onclick="closeRecovery()">Fechar</button>
        </div>
    </div>
</div>

<div class="main-wrapper">
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="" method="POST">
                <h1>Criar Conta</h1>
                <input type="text" name="nome" placeholder="Nome" required />
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <button type="submit" name="btn_registar" class="btn-neon">Registar</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="" method="POST">
                <h1>Entrar</h1>
                <input type="email" name="email" placeholder="Email" required />
                <input type="password" name="password" placeholder="Password" required />
                <a href="#" class="forgot" onclick="openRecoveryStep(1); return false;">Esqueceste-te da password?</a>
                <button type="submit" name="btn_login" class="btn-neon">Entrar</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Bem-vindo!</h1>
                    <p>Já tens conta? Faz login para entrar.</p>
                    <button class="ghost" id="signIn">Entrar</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Olá, Fã!</h1>
                    <p>Cria a tua conta para seguires os teus grupos.</p>
                    <button class="ghost" id="signUp">Registar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('signUp').addEventListener('click', () => document.getElementById('container').classList.add("right-panel-active"));
    document.getElementById('signIn').addEventListener('click', () => document.getElementById('container').classList.remove("right-panel-active"));

    const videos = document.querySelectorAll('.video-wrapper');
    let currentVideo = 0;
    setInterval(() => { videos[currentVideo].classList.remove('active'); currentVideo = (currentVideo+1)%videos.length; videos[currentVideo].classList.add('active'); }, 10000);

    function openRecoveryStep(step) {
        document.getElementById('recoveryModal').classList.add('active');
        document.querySelectorAll('.modal-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + step).classList.add('active');
    }
    function closeRecovery() { document.getElementById('recoveryModal').classList.remove('active'); }
    document.getElementById('recoveryModal').addEventListener('click', function(e) { if (e.target === this) closeRecovery(); });

    <?php if (isset($recovery_step)): ?>
    openRecoveryStep(<?php echo (int) $recovery_step; ?>);
    <?php endif; ?>
    <?php if (isset($recovery_error)): ?>
    document.getElementById('recovery_error').textContent = <?php echo json_encode($recovery_error, JSON_UNESCAPED_UNICODE); ?>;
    <?php endif; ?>
</script>
</body>
</html>