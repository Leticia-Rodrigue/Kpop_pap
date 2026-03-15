<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'bd_connection.php';
require_once __DIR__ . '/csrf_helper.php';

// Segurança para Admin/Master
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) {
    header("Location: index.php");
    exit();
}

$msg = "";
$msg_type = "";
$isCsrfValid = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
    $msg = "Pedido inválido. Atualiza a página e tenta novamente.";
    $msg_type = "error";
    $isCsrfValid = false;
}

// Lógica para Salvar/Editar
if ($isCsrfValid && isset($_POST['btn_save_group'])) {
    $nome    = mysqli_real_escape_string($conn, $_POST['nome_grupo']);
    $tag     = mysqli_real_escape_string($conn, $_POST['tag']);
    $genero  = mysqli_real_escape_string($conn, $_POST['genero']);
    $desc    = mysqli_real_escape_string($conn, $_POST['descricao']);
    $foto    = mysqli_real_escape_string($conn, $_POST['foto_url']); 
    $membros = mysqli_real_escape_string($conn, $_POST['membros']);
    $empresa = mysqli_real_escape_string($conn, $_POST['empresa']);
    $debut   = mysqli_real_escape_string($conn, $_POST['data_debut']);
    $insta   = mysqli_real_escape_string($conn, $_POST['insta_link']);
    $yt      = mysqli_real_escape_string($conn, $_POST['yt_link']);
    $tt      = mysqli_real_escape_string($conn, $_POST['tt_link']);

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $id = intval($_POST['edit_id']);
        $sql = "UPDATE grupos SET nome_grupo='$nome', tag='$tag', genero='$genero', descricao='$desc', foto_url='$foto', membros='$membros', empresa='$empresa', data_debut='$debut', insta_link='$insta', yt_link='$yt', tt_link='$tt' WHERE id_grupo=$id";
    } else {
        $sql = "INSERT INTO grupos (nome_grupo, tag, genero, descricao, foto_url, membros, empresa, data_debut, insta_link, yt_link, tt_link) VALUES ('$nome', '$tag', '$genero', '$desc', '$foto', '$membros', '$empresa', '$debut', '$insta', '$yt', '$tt')";
    }
    if (mysqli_query($conn, $sql)) { header("Location: gerir_grupos.php?ok=1"); exit(); }
}

// Lógica para Apagar
if ($isCsrfValid && isset($_POST['btn_delete_group'])) {
    $id = intval($_POST['delete_id'] ?? 0);
    if ($id > 0 && mysqli_query($conn, "DELETE FROM grupos WHERE id_grupo = $id")) {
        header("Location: gerir_grupos.php?ok=del");
        exit();
    }

    $msg = "Erro ao apagar grupo: " . mysqli_error($conn);
    $msg_type = "error";
}

if (isset($_GET['ok'])) {
    $msg = $_GET['ok'] === 'del' ? "Grupo apagado com sucesso!" : "Grupo guardado com sucesso!";
    $msg_type = "success";
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $res = mysqli_query($conn, "SELECT * FROM grupos WHERE id_grupo = ".intval($_GET['edit']));
    $edit_data = mysqli_fetch_assoc($res);
}

$grupos = mysqli_query($conn, "SELECT * FROM grupos ORDER BY id_grupo DESC");
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="referrer" content="no-referrer">
    <title>Master Control | K-Universe</title>
    <link rel="stylesheet" href="css/style_admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        
        /* ÁREA PRINCIPAL (Conforme a imagem 2) */
        .main-content {
            margin-left: 280px;
            padding: 40px;
            width: 100%;
            background-color: var(--dark-bg);
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 30px;
        }

        /* CONTAINER DO FORMULÁRIO (Imagem 2) */
        .form-container {
            background-color: #333333;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 40px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
        }

        input, textarea, select {
            background-color: var(--input-bg) !important;
            border: 1px solid #555 !important;
            color: #ccc !important;
            padding: 12px 15px;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
            font-size: 0.85rem;
            margin-bottom: 10px;
        }

        .full-width { grid-column: span 3; }

        /* BOTÃO GRADIENTE (Imagem 2) */
        .btn-registar {
            grid-column: span 3;
            background: linear-gradient(90deg, #ff00ff 0%, #6b73ff 50%, #00d4ff 100%);
            border: none;
            padding: 15px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-registar:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
        }

        /* LISTAGEM (Imagem 2) */
        .registados-title {
            color: var(--magenta);
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 20px;
        }

        .group-card {
            background-color: #1a1a1a;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 12px;
            border: 1px solid #222;
        }

        .group-img {
            width: 70px;
            height: 70px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #333;
        }

        .group-info h3 { margin: 0; font-size: 1.1rem; }
        .group-info p { margin: 5px 0 0; color: #666; font-size: 0.8rem; }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .alert.success { background: rgba(0,255,136,0.1); color: #00ff88; border: 1px solid rgba(0,255,136,0.3); }
        .alert.error { background: rgba(255,77,77,0.1); color: #ff4d4d; border: 1px solid rgba(255,77,77,0.3); }

        .actions { margin-left: auto; display: flex; gap: 10px; }
        .actions a,
        .actions button { 
            color: #fff; 
            text-decoration: none; 
            font-size: 0.75rem; 
            font-weight: 700; 
            padding: 6px 12px; 
            border-radius: 6px; 
            border: 1px solid #444; 
            background: transparent;
            cursor: pointer;
            font-family: inherit;
        }
    </style>
</head>
<body class="admin-body">
    <aside class="sidebar-master">
        <div class="sidebar-brand">MASTER CONTROL</div>
        <nav class="sidebar-nav">
            <a href="painel_adminMaster.php" class="nav-link">Dashboard Global</a>
            <a href="gerir_grupos.php" class="nav-link active">Adicionar Grupos</a>
            <a href="gerir_produtos.php" class="nav-link">Adicionar Produtos</a>
            <a href="gerir_noticias.php" class="nav-link">Adicionar Notícias & Agenda</a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-link">Ver Site</a>
        </nav>
    </aside>

    <main class="main-content">
        <h1>Adicionar Novo Grupo</h1>

        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="edit_id" value="<?php echo $edit_data['id_grupo'] ?? ''; ?>">
                
                <input type="text" name="nome_grupo" placeholder="Nome do Grupo" value="<?php echo $edit_data['nome_grupo'] ?? ''; ?>" required>
                <input type="text" name="tag" placeholder="Tag (Ex: 4th Gen)" value="<?php echo $edit_data['tag'] ?? ''; ?>">
                <select name="genero">
                    <option value="Feminino" <?php if(($edit_data['genero']??'')=='Feminino') echo 'selected'; ?>>Feminino</option>
                    <option value="Masculino" <?php if(($edit_data['genero']??'')=='Masculino') echo 'selected'; ?>>Masculino</option>
                </select>

                <textarea name="descricao" class="full-width" rows="3" placeholder="Descrição..."><?php echo $edit_data['descricao'] ?? ''; ?></textarea>

                <input type="text" name="empresa" placeholder="Empresa" value="<?php echo $edit_data['empresa'] ?? ''; ?>">
                <input type="text" name="membros" placeholder="Membros" value="<?php echo $edit_data['membros'] ?? ''; ?>">
                <input type="text" name="data_debut" placeholder="Ano Debut" value="<?php echo $edit_data['data_debut'] ?? ''; ?>">

                <input type="text" name="foto_url" class="full-width" placeholder="Link da Imagem (URL)" value="<?php echo $edit_data['foto_url'] ?? ''; ?>" required>

                <input type="text" name="insta_link" placeholder="Instagram" value="<?php echo $edit_data['insta_link'] ?? ''; ?>">
                <input type="text" name="yt_link" placeholder="YouTube" value="<?php echo $edit_data['yt_link'] ?? ''; ?>">
                <input type="text" name="tt_link" placeholder="TikTok" value="<?php echo $edit_data['tt_link'] ?? ''; ?>">

                <button type="submit" name="btn_save_group" class="btn-registar">REGISTAR NO UNIVERSO</button>
            </form>
        </div>

        <h2 class="registados-title">Grupos Registados</h2>
        
        <div class="list-container">
            <?php while($g = mysqli_fetch_assoc($grupos)): ?>
            <div class="group-card">
                <img src="<?php echo $g['foto_url']; ?>" class="group-img" referrerpolicy="no-referrer" onerror="this.src='https://via.placeholder.com/70/000/fff?text=?';">
                <div class="group-info">
                    <h3><?php echo $g['nome_grupo']; ?></h3>
                    <p><?php echo $g['empresa']; ?> • <?php echo $g['tag']; ?></p>
                </div>
                <div class="actions">
                    <a href="?edit=<?php echo $g['id_grupo']; ?>" style="border-color: #00d4ff;">EDITAR</a>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Apagar?')">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="delete_id" value="<?php echo (int) $g['id_grupo']; ?>">
                        <button type="submit" name="btn_delete_group" style="border-color: #ff4d4d;">APAGAR</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

</body>
</html>