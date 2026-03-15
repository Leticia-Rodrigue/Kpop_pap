<?php
session_start();
include 'bd_connection.php';
require_once __DIR__ . '/csrf_helper.php';

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

// --- ADICIONAR OU ATUALIZAR NOTÍCIA ---
if ($isCsrfValid && isset($_POST['btn_save_news'])) {
    $titulo   = mysqli_real_escape_string($conn, $_POST['titulo']);
    $resumo   = mysqli_real_escape_string($conn, $_POST['resumo']);
    $data_pub = $_POST['data_pub'];
    $imagem   = mysqli_real_escape_string($conn, $_POST['imagem']);
    $link_url = mysqli_real_escape_string($conn, $_POST['link_url']);

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $id  = intval($_POST['edit_id']);
        $sql = "UPDATE noticias SET titulo='$titulo', resumo='$resumo', data_pub='$data_pub', imagem='$imagem', link_url='$link_url' WHERE id_noticia=$id";
    } else {
        $sql = "INSERT INTO noticias (titulo, resumo, data_pub, imagem, link_url) VALUES ('$titulo', '$resumo', '$data_pub', '$imagem', '$link_url')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: gerir_noticias.php?ok=news");
        exit();
    } else {
        error_log('Erro a guardar noticia: ' . mysqli_error($conn));
        $msg      = "Nao foi possivel guardar a noticia.";
        $msg_type = "error";
    }
}

// --- ADICIONAR OU ATUALIZAR AGENDA ---
if ($isCsrfValid && isset($_POST['btn_save_agenda'])) {
    $data_ev = mysqli_real_escape_string($conn, $_POST['data_evento']);
    $grupo   = mysqli_real_escape_string($conn, $_POST['grupo_evento']);
    $local   = mysqli_real_escape_string($conn, $_POST['localizacao']);
    $estado  = mysqli_real_escape_string($conn, $_POST['estado']);
    $link    = mysqli_real_escape_string($conn, $_POST['link_bilhetes']);
    $ordem   = $_POST['ordem_data'];

    if (isset($_POST['edit_agenda_id']) && !empty($_POST['edit_agenda_id'])) {
        $id  = intval($_POST['edit_agenda_id']);
        $sql = "UPDATE agenda_shows SET data_evento='$data_ev', grupo_evento='$grupo', localizacao='$local', estado='$estado', link_bilhetes='$link', ordem_data='$ordem' WHERE id_show=$id";
    } else {
        $sql = "INSERT INTO agenda_shows (data_evento, grupo_evento, localizacao, estado, link_bilhetes, ordem_data) VALUES ('$data_ev', '$grupo', '$local', '$estado', '$link', '$ordem')";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: gerir_noticias.php?ok=agenda#agenda_section");
        exit();
    } else {
        error_log('Erro a guardar show na agenda: ' . mysqli_error($conn));
        $msg      = "Nao foi possivel guardar o show na agenda.";
        $msg_type = "error";
    }
}

// --- APAGAR ---
if ($isCsrfValid && isset($_POST['btn_delete_news'])) {
    $id = intval($_POST['delete_news_id'] ?? 0);
    if ($id > 0 && mysqli_query($conn, "DELETE FROM noticias WHERE id_noticia = $id")) {
        header("Location: gerir_noticias.php?ok=news");
        exit();
    } else {
        error_log('Erro a apagar noticia: ' . mysqli_error($conn));
        $msg = "Nao foi possivel remover a noticia.";
        $msg_type = "error";
    }
}
if ($isCsrfValid && isset($_POST['btn_delete_agenda'])) {
    $id = intval($_POST['delete_agenda_id'] ?? 0);
    if ($id > 0 && mysqli_query($conn, "DELETE FROM agenda_shows WHERE id_show = $id")) {
        header("Location: gerir_noticias.php?ok=agenda#agenda_section");
        exit();
    } else {
        error_log('Erro a apagar show: ' . mysqli_error($conn));
        $msg = "Nao foi possivel remover o show.";
        $msg_type = "error";
    }
}

// Mensagens de sucesso
if (isset($_GET['ok'])) {
    $msg      = $_GET['ok'] === 'news' ? "Notícia guardada com sucesso!" : "Show guardado na agenda com sucesso!";
    $msg_type = "success";
}

// --- CARREGAR DADOS PARA EDIÇÃO ---
$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit   = intval($_GET['edit']);
    $res       = mysqli_query($conn, "SELECT * FROM noticias WHERE id_noticia = $id_edit");
    if ($res) $edit_data = mysqli_fetch_assoc($res);
}

$edit_agenda = null;
if (isset($_GET['edit_agenda'])) {
    $id_edit_ag = intval($_GET['edit_agenda']);
    $res_ag     = mysqli_query($conn, "SELECT * FROM agenda_shows WHERE id_show = $id_edit_ag");
    if ($res_ag) $edit_agenda = mysqli_fetch_assoc($res_ag);
}

$noticias = mysqli_query($conn, "SELECT * FROM noticias ORDER BY id_noticia DESC");
$agenda   = mysqli_query($conn, "SELECT * FROM agenda_shows ORDER BY ordem_data ASC");

if (!$noticias) { error_log('Erro a carregar noticias: ' . mysqli_error($conn)); $msg = "Nao foi possivel carregar as noticias."; $msg_type = "error"; }
if (!$agenda)   { error_log('Erro a carregar agenda: ' . mysqli_error($conn)); $msg = "Nao foi possivel carregar a agenda."; $msg_type = "error"; }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | Gestão de Notícias e Agenda</title>
    <link rel="stylesheet" href="css/style_admin.css">
    <style>
        .form-container-glow {
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid #333;
            border-radius: 20px; padding: 30px; margin-bottom: 30px;
        }
        .master-form-grid input,
        .master-form-grid textarea,
        .master-form-grid select {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid #444 !important;
            border-radius: 10px !important;
            padding: 15px !important;
            color: white !important;
            width: 100%; margin-bottom: 10px;
            box-sizing: border-box;
            font-family: inherit; font-size: 0.9rem;
        }
        .master-form-grid select { appearance: none; -webkit-appearance: none; cursor: pointer; }
        .master-form-grid select option { background: #1a1a1a; color: white; }
        .master-form-grid textarea { resize: vertical; }
        .btn-flex { display: flex; gap: 10px; }
        .btn-save-custom {
            background: linear-gradient(45deg, #00f2fe, #4facfe);
            border: none; border-radius: 10px; padding: 15px;
            color: black; font-weight: 800; cursor: pointer;
            text-transform: uppercase; flex: 2; transition: 0.3s;
        }
        .btn-save-custom:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(79,172,254,0.4); }
        .btn-cancel {
            background: #222; color: #fff; border: 1px solid #444;
            border-radius: 10px; padding: 15px; text-decoration: none;
            font-weight: 800; text-align: center; flex: 1;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-agenda { background: linear-gradient(45deg, #f09, #ff0080); color: white; }
        .btn-agenda:hover { box-shadow: 0 5px 20px rgba(255,0,128,0.4); }
        .news-card-admin, .agenda-card-admin {
            background: rgba(15, 15, 15, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px; padding: 20px; transition: 0.3s; margin-bottom: 10px;
        }
        .news-card-admin:hover, .agenda-card-admin:hover { border-color: #4facfe; }
        .action-btn { padding: 8px 15px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-decoration: none; display: inline-block; transition: 0.2s; background: transparent; cursor: pointer; font-family: inherit; }
        .btn-edit   { color: #4facfe; border: 1px solid #4facfe; }
        .btn-edit:hover   { background: #4facfe; color: black; }
        .btn-delete { color: #ff4d4d; border: 1px solid #ff4d4d; }
        .btn-delete:hover { background: #ff4d4d; color: white; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: bold; font-size: 0.9rem; }
        .alert.success { background: rgba(0,255,136,0.1); color: #00ff88; border: 1px solid rgba(0,255,136,0.3); }
        .alert.error   { background: rgba(255,77,77,0.1); color: #ff4d4d; border: 1px solid rgba(255,77,77,0.3); font-family: monospace; }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar-master">
        <div class="sidebar-brand">MASTER CONTROL</div>
        <nav class="sidebar-nav">
            <a href="painel_adminMaster.php" class="nav-link">Dashboard Global</a>
            <a href="gerir_grupos.php" class="nav-link">Adicionar Grupos</a>
            <a href="gerir_produtos.php" class="nav-link">Adicionar Produtos</a>
            <a href="gerir_noticias.php" class="nav-link active">Adicionar Notícias & Agenda</a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-link">Ver Site</a>
        </nav>
    </aside>

    <main class="admin-main">

        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <!-- SECÇÃO NOTÍCIAS -->
        <header class="main-header">
            <h1><?php echo $edit_data ? "Editar Notícia" : "Publicar Notícia"; ?></h1>
        </header>

        <section class="form-section">
            <div class="form-container-glow" style="max-width: 850px; border-color: <?php echo $edit_data ? '#4facfe' : '#333'; ?>;">
                <form method="POST" class="master-form-grid">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['id_noticia'] ?? ''; ?>">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                        <input type="text" name="titulo" placeholder="Título da Notícia" value="<?php echo htmlspecialchars($edit_data['titulo'] ?? ''); ?>" required>
                        <input type="date" name="data_pub" value="<?php echo $edit_data['data_pub'] ?? date('Y-m-d'); ?>" required>
                    </div>
                    <textarea name="resumo" placeholder="Resumo..." rows="4" required><?php echo htmlspecialchars($edit_data['resumo'] ?? ''); ?></textarea>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <input type="text" name="imagem" placeholder="URL da Imagem" value="<?php echo htmlspecialchars($edit_data['imagem'] ?? ''); ?>" required>
                        <input type="text" name="link_url" placeholder="Link Externo" value="<?php echo htmlspecialchars($edit_data['link_url'] ?? ''); ?>">
                    </div>
                    <div class="btn-flex">
                        <button type="submit" name="btn_save_news" class="btn-save-custom">
                            <?php echo $edit_data ? "Atualizar Notícia" : "Lançar Notícia"; ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="gerir_noticias.php" class="btn-cancel">CANCELAR</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- SECÇÃO AGENDA -->
        <header class="main-header" id="agenda_section">
            <h1><?php echo $edit_agenda ? "Editar Show" : "Adicionar Show"; ?></h1>
        </header>

        <section class="form-section">
            <div class="form-container-glow" style="max-width: 850px; border-color: #f09;">
                <form method="POST" class="master-form-grid">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="edit_agenda_id" value="<?php echo $edit_agenda['id_show'] ?? ''; ?>">
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                        <input type="text" name="data_evento" placeholder="Data (Ex: 06-08 MAR)" value="<?php echo htmlspecialchars($edit_agenda['data_evento'] ?? ''); ?>" required>
                        <input type="text" name="grupo_evento" placeholder="Artista / Grupo" value="<?php echo htmlspecialchars($edit_agenda['grupo_evento'] ?? ''); ?>" required>
                    </div>
                    <input type="text" name="localizacao" placeholder="Localização" value="<?php echo htmlspecialchars($edit_agenda['localizacao'] ?? ''); ?>" required>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <select name="estado">
                            <option value="EM BREVE"   <?php if(($edit_agenda['estado'] ?? '') == 'EM BREVE')   echo 'selected'; ?>>EM BREVE</option>
                            <option value="BILHETES"   <?php if(($edit_agenda['estado'] ?? '') == 'BILHETES')   echo 'selected'; ?>>BILHETES</option>
                            <option value="FINALIZADO" <?php if(($edit_agenda['estado'] ?? '') == 'FINALIZADO') echo 'selected'; ?>>FINALIZADO</option>
                        </select>
                        <input type="date" name="ordem_data" value="<?php echo $edit_agenda['ordem_data'] ?? date('Y-m-d'); ?>" required>
                        <input type="text" name="link_bilhetes" placeholder="Link Bilhetes" value="<?php echo htmlspecialchars($edit_agenda['link_bilhetes'] ?? ''); ?>">
                    </div>
                    <div class="btn-flex">
                        <button type="submit" name="btn_save_agenda" class="btn-save-custom btn-agenda">
                            <?php echo $edit_agenda ? "Atualizar Agenda" : "Salvar na Agenda"; ?>
                        </button>
                        <?php if($edit_agenda): ?>
                            <a href="gerir_noticias.php#agenda_section" class="btn-cancel">CANCELAR</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <!-- LISTA NOTÍCIAS -->
        <h2 style="margin-top: 50px;">
            Feed de Notícias <?php if($noticias) echo "(" . mysqli_num_rows($noticias) . ")"; ?>
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
            <?php if($noticias): while($n = mysqli_fetch_assoc($noticias)): ?>
            <div class="news-card-admin">
                <img src="<?php echo htmlspecialchars($n['imagem']); ?>" style="width: 100%; height: 150px; object-fit: cover; border-radius: 10px;" onerror="this.src='https://via.placeholder.com/320x150?text=Sem+Imagem'">
                <h3 style="margin-top:10px;"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                <small style="color:#666;"><?php echo $n['data_pub']; ?></small>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <a href="?edit=<?php echo $n['id_noticia']; ?>" class="action-btn btn-edit">EDITAR</a>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Apagar notícia?')">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="delete_news_id" value="<?php echo (int) $n['id_noticia']; ?>">
                        <button type="submit" name="btn_delete_news" class="action-btn btn-delete">REMOVER</button>
                    </form>
                </div>
            </div>
            <?php endwhile; else: ?>
                <p style="color:#666;">Nenhuma notícia publicada.</p>
            <?php endif; ?>
        </div>

        <!-- LISTA AGENDA -->
        <h2 style="margin-top: 50px; color: #f09;">
            Agenda de Shows <?php if($agenda) echo "(" . mysqli_num_rows($agenda) . ")"; ?>
        </h2>
        <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
            <?php if($agenda): while($a = mysqli_fetch_assoc($agenda)): ?>
            <div class="agenda-card-admin" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="color: #f09;"><?php echo htmlspecialchars($a['data_evento']); ?></strong> —
                    <span><?php echo htmlspecialchars($a['grupo_evento']); ?></span> |
                    <small style="color: #bbb;"><?php echo htmlspecialchars($a['localizacao']); ?></small>
                    <span style="margin-left:10px; font-size: 0.7rem; background:#333; padding: 2px 8px; border-radius: 4px;"><?php echo htmlspecialchars($a['estado']); ?></span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="?edit_agenda=<?php echo $a['id_show']; ?>#agenda_section" class="action-btn btn-edit">EDITAR</a>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Apagar este show?')">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="delete_agenda_id" value="<?php echo (int) $a['id_show']; ?>">
                        <button type="submit" name="btn_delete_agenda" class="action-btn btn-delete">REMOVER</button>
                    </form>
                </div>
            </div>
            <?php endwhile; else: ?>
                <p style="color:#666;">Nenhum show na agenda.</p>
            <?php endif; ?>
        </div>

    </main>
</body>
</html>