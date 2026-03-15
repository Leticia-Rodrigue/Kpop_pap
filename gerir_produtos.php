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

if ($isCsrfValid && isset($_POST['btn_save_prod'])) {
    $nome      = mysqli_real_escape_string($conn, $_POST['nome']);
    $preco     = floatval($_POST['preco']);
    $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
    $grupo     = mysqli_real_escape_string($conn, $_POST['grupo'] ?? '');
    $url_img   = mysqli_real_escape_string($conn, $_POST['url_img']);
    $desc      = mysqli_real_escape_string($conn, $_POST['descricao'] ?? '');

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $id  = intval($_POST['edit_id']);
        $sql = "UPDATE produtos SET nome='$nome', preco='$preco', categoria='$categoria', grupo='$grupo', imagem_url='$url_img', descricao='$desc' WHERE id_produto=$id";
    } else {
        $sql = "INSERT INTO produtos (nome, preco, categoria, grupo, imagem_url, descricao) VALUES ('$nome', '$preco', '$categoria', '$grupo', '$url_img', '$desc')";
    }

    $result = mysqli_query($conn, $sql);
    if ($result) {
        header("Location: gerir_produtos.php?ok=1");
        exit();
    } else {
        $msg      = "Erro SQL: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

if (isset($_GET['ok'])) {
    $msg      = "Produto guardado com sucesso!";
    $msg_type = "success";
}

if ($isCsrfValid && isset($_POST['btn_delete_prod'])) {
    $id = intval($_POST['delete_id'] ?? 0);
    if ($id > 0 && mysqli_query($conn, "DELETE FROM produtos WHERE id_produto = $id")) {
        header("Location: gerir_produtos.php?ok=1");
        exit();
    } else {
        $msg      = "Erro ao apagar: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id_edit   = intval($_GET['edit']);
    $res       = mysqli_query($conn, "SELECT * FROM produtos WHERE id_produto = $id_edit");
    $edit_data = mysqli_fetch_assoc($res);
}

$produtos = mysqli_query($conn, "SELECT * FROM produtos ORDER BY id_produto DESC");
if (!$produtos) {
    $msg      = "Erro ao carregar produtos: " . mysqli_error($conn);
    $msg_type = "error";
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | Gestão de Stock</title>
    <link rel="stylesheet" href="css/style_admin.css">
    <style>
        .form-container-glow {
            background: rgba(20, 20, 20, 0.6);
            backdrop-filter: blur(15px);
            border: 1px solid <?php echo $edit_data ? 'var(--c-neon)' : '#333'; ?>;
            border-radius: 20px;
            padding: 30px;
            box-shadow: <?php echo $edit_data ? '0 0 20px rgba(0, 255, 255, 0.1)' : 'none'; ?>;
        }
        .master-form-grid input,
        .master-form-grid select {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid #444 !important;
            border-radius: 10px !important;
            padding: 15px !important;
            color: white !important;
            transition: 0.3s;
            width: 100%;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 0.9rem;
        }
        .master-form-grid input:focus,
        .master-form-grid select:focus {
            border-color: var(--p-neon) !important;
            box-shadow: 0 0 10px rgba(255, 0, 255, 0.2);
            outline: none;
        }
        .master-form-grid select { appearance: none; -webkit-appearance: none; cursor: pointer; }
        .master-form-grid select option { background: #1a1a1a; color: white; }
        .btn-save-custom {
            background: linear-gradient(45deg, #ff00ff, #8000ff);
            border: none; border-radius: 10px; padding: 15px;
            color: white; font-weight: 800; cursor: pointer;
            text-transform: uppercase; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-save-custom:hover { transform: translateY(-3px); box-shadow: 0 5px 20px rgba(255, 0, 255, 0.4); }
        .product-card-admin {
            background: rgba(15, 15, 15, 0.9) !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
        }
        .product-card-admin:hover { transform: translateY(-10px); border-color: var(--c-neon) !important; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .action-btn { padding: 8px 15px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; text-decoration: none; transition: 0.3s; background: transparent; cursor: pointer; font-family: inherit; }
        .btn-edit   { color: var(--c-neon); border: 1px solid var(--c-neon); }
        .btn-edit:hover   { background: var(--c-neon); color: black; }
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
            <a href="gerir_produtos.php" class="nav-link active">Adicionar Produtos</a>
            <a href="gerir_noticias.php" class="nav-link">Adicionar Notícias & Agenda</a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-link">Ver Site</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="main-header">
            <h1 style="font-size: 2.5rem; letter-spacing: -1px;">
                <?php echo $edit_data ? "Editar Produto" : "Gestão de Stock"; ?>
            </h1>
        </header>

        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <section class="form-section">
            <div class="form-container-glow" style="width: 100%; max-width: 850px;">
                <form method="POST" class="master-form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_data['id_produto'] ?? ''; ?>">

                    <input type="text" name="nome" placeholder="Nome do Produto" value="<?php echo htmlspecialchars($edit_data['nome'] ?? ''); ?>" required>
                    <input type="number" step="0.01" min="0" name="preco" placeholder="Preço (€)" value="<?php echo $edit_data['preco'] ?? ''; ?>" required>

                    <select name="categoria" required>
                        <option value="" disabled <?php echo !$edit_data ? 'selected' : ''; ?>>-- Categoria --</option>
                        <?php foreach(['Álbuns','Lightsticks','Vestuário','Porta-chaves','Peluches'] as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo (($edit_data['categoria'] ?? '') === $cat) ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="grupo">
                        <option value="" <?php echo empty($edit_data['grupo']) ? 'selected' : ''; ?>>-- Grupo (opcional) --</option>
                        <?php foreach(['BTS','Blackpink','Stray Kids','IVE','RIIZE'] as $grp): ?>
                            <option value="<?php echo $grp; ?>" <?php echo (($edit_data['grupo'] ?? '') === $grp) ? 'selected' : ''; ?>><?php echo $grp; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="url_img" placeholder="URL da Imagem" value="<?php echo htmlspecialchars($edit_data['imagem_url'] ?? ''); ?>" required>
                    <input type="text" name="descricao" placeholder="Breve descrição" value="<?php echo htmlspecialchars($edit_data['descricao'] ?? ''); ?>">

                    <div style="grid-column: span 2; display: flex; gap: 15px; margin-top: 10px;">
                        <button type="submit" name="btn_save_prod" class="btn-save-custom" style="flex: 2;">
                            <?php echo $edit_data ? "Guardar Alterações" : "Adicionar Produto"; ?>
                        </button>
                        <?php if($edit_data): ?>
                            <a href="gerir_produtos.php" class="btn-save-custom" style="flex: 1; background: #222; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">CANCELAR</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </section>

        <h2 style="margin: 50px 0 25px 0; font-weight: 300; color: #888;">
            Produtos Ativos <?php if($produtos) echo "(" . mysqli_num_rows($produtos) . ")"; ?>
        </h2>

        <div class="grid-admin-view" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
            <?php if ($produtos): while($p = mysqli_fetch_assoc($produtos)): ?>
            <div class="product-card-admin" style="padding: 25px; border-radius: 20px; text-align: center;">
                <div style="overflow: hidden; border-radius: 12px; height: 200px; margin-bottom: 20px;">
                    <img src="<?php echo htmlspecialchars($p['imagem_url']); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='https://via.placeholder.com/280x200?text=Sem+Imagem'">
                </div>
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($p['nome']); ?></h3>
                <div style="color: var(--c-neon); font-size: 1.4rem; font-weight: 800; margin-bottom: 5px;">
                    <?php echo number_format($p['preco'], 2); ?>€
                </div>
                <div style="font-size: 0.75rem; color: #555; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 2px;">
                    <?php echo htmlspecialchars($p['categoria']); ?>
                </div>
                <?php if (!empty($p['grupo'])): ?>
                <div style="font-size: 0.75rem; color: #00ffff; margin-bottom: 15px; letter-spacing: 1px;">
                    <?php echo htmlspecialchars($p['grupo']); ?>
                </div>
                <?php else: ?>
                <div style="margin-bottom: 15px;"></div>
                <?php endif; ?>
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <a href="?edit=<?php echo $p['id_produto']; ?>" class="action-btn btn-edit">Editar</a>
                    <form method="POST" style="margin: 0;" onsubmit="return confirm('Apagar este produto permanentemente?')">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="delete_id" value="<?php echo (int) $p['id_produto']; ?>">
                        <button type="submit" name="btn_delete_prod" class="action-btn btn-delete">Remover</button>
                    </form>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </main>

</body>
</html>