<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'bd_connection.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: registar.php"); 
    exit(); 
}

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "success";

// LÓGICA DE GRAVAÇÃO
if (isset($_POST['btn_save'])) {
    $nome_post  = mysqli_real_escape_string($conn, trim($_POST['display_name']));
    $email_post = mysqli_real_escape_string($conn, trim($_POST['email']));
    $nova_pass  = trim($_POST['nova_password']);

    // Atualiza Nome e Email
    $sql_update = "UPDATE utilizadores SET nome='$nome_post', email='$email_post' WHERE id_utilizador = $user_id";
    $res_update = mysqli_query($conn, $sql_update);

    if (!$res_update) {
        error_log('Erro a atualizar perfil: ' . mysqli_error($conn));
        $msg = "Nao foi possivel atualizar os teus dados.";
        $msg_type = "error";
    } else {
        $_SESSION['username'] = $nome_post;
        $msg = "Dados atualizados com sucesso!";

        // Atualiza Password se preenchida
        if (!empty($nova_pass)) {
            $hash = password_hash($nova_pass, PASSWORD_DEFAULT);
            $sql_pass = "UPDATE utilizadores SET senha_hash='$hash' WHERE id_utilizador = $user_id";
            $res_pass = mysqli_query($conn, $sql_pass);


            if ($res_pass) {
                $msg = "Perfil e password atualizados com sucesso!";
            } else {
                error_log('Erro a atualizar password no perfil: ' . mysqli_error($conn));
                $msg = "Os dados foram atualizados, mas nao foi possivel mudar a password.";
                $msg_type = "error";
            }
        }
    }
}

// BUSCAR DADOS ATUALIZADOS
$query_perfil = mysqli_query($conn, "SELECT * FROM utilizadores WHERE id_utilizador = $user_id");
if (!$query_perfil) {
    error_log('Erro a carregar perfil: ' . mysqli_error($conn));
    http_response_code(500);
    die("Nao foi possivel carregar o perfil.");
}
$dados_utilizador = mysqli_fetch_assoc($query_perfil);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>K-Pop Universe | Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style_grupos.css">
    <style>
        .profile-edit-container {
            background: rgba(20, 20, 20, 0.95);
            border-radius: 15px;
            padding: 40px;
            border: 1px solid #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .input-group-perfil { margin-bottom: 25px; }
        .input-group-perfil label { 
            display: block; 
            color: #ff0080; 
            font-size: 11px; 
            font-weight: bold; 
            letter-spacing: 1.5px;
            margin-bottom: 10px; 
        }
        .input-group-perfil input {
            width: 100%; 
            background: #111; 
            border: 1px solid #333;
            padding: 15px; 
            color: #fff; 
            border-radius: 8px; 
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .input-group-perfil input:focus { border-color: #ff0080; box-shadow: 0 0 10px rgba(255, 0, 128, 0.2); }
        .btn-save {
            background: #ff0080; 
            color: #fff; 
            border: none; 
            padding: 15px;
            border-radius: 8px; 
            font-weight: bold; 
            cursor: pointer; 
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-save:hover { background: #d6006c; transform: translateY(-2px); }
        .msg-success { color: #00ff88; text-align: center; margin-top: 20px; font-weight: bold; }
        .msg-error   { color: #ff4444; text-align: center; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

    <div class="section-header" style="padding-top: 120px;">
        <div class="section-title" data-aos="zoom-in">
            <span class="line"></span>
            <h1>Definições de Conta</h1>
            <span class="line"></span>
        </div>
    </div>

    <main class="main-layout">
        <section class="content-area">
            <form method="POST" class="profile-edit-container" data-aos="fade-up">
                
                <div class="input-group-perfil">
                    <label>NOME DE EXIBIÇÃO</label>
                    <input type="text" name="display_name" value="<?php echo htmlspecialchars($dados_utilizador['nome']); ?>" required>
                </div>

                <div class="input-group-perfil">
                    <label>ENDEREÇO DE E-MAIL</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($dados_utilizador['email']); ?>" required>
                </div>

                <div class="input-group-perfil">
                    <label>ALTERAR PALAVRA-PASSE</label>
                    <input type="password" name="nova_password" placeholder="Deixe em branco para não alterar">
                </div>

                <button type="submit" name="btn_save" class="btn-save">Atualizar Dados</button>

                <?php if ($msg): ?>
                    <p class="msg-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></p>
                <?php endif; ?>
            </form>
        </section>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ once: true });</script>
</body>
</html>