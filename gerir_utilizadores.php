<?php
session_start();
include 'bd_connection.php';
require_once __DIR__ . '/csrf_helper.php';

// 1. SEGURANÇA: Apenas Master (Role 1) costuma gerir utilizadores
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
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

// 2. LÓGICA: ALTERAR CARGO (Role)
if ($isCsrfValid && isset($_POST['btn_update_role'])) {
    $id = intval($_POST['user_id'] ?? 0);
    $new_role = intval($_POST['new_role'] ?? 0);

    if ($id === intval($_SESSION['user_id'])) {
        $msg = "Não podes alterar o teu próprio cargo nesta página.";
        $msg_type = "error";
    } elseif (($new_role !== 2 && $new_role !== 3) || $id <= 0) {
        $msg = "Operação inválida.";
        $msg_type = "error";
    } elseif (mysqli_query($conn, "UPDATE utilizadores SET role = $new_role WHERE id_utilizador = $id")) {
        header("Location: gerir_utilizadores.php?ok=1");
        exit();
    } else {
        $msg = "Erro ao atualizar cargo: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

// 3. LÓGICA: BANIR/REMOVER
if ($isCsrfValid && isset($_POST['btn_delete_user'])) {
    $id = intval($_POST['delete_user_id'] ?? 0);

    if ($id === intval($_SESSION['user_id'])) {
        $msg = "Não podes remover a tua própria conta nesta página.";
        $msg_type = "error";
    } elseif ($id <= 0) {
        $msg = "Operação inválida.";
        $msg_type = "error";
    } elseif (mysqli_query($conn, "DELETE FROM utilizadores WHERE id_utilizador = $id")) {
        header("Location: gerir_utilizadores.php?ok=1");
        exit();
    } else {
        $msg = "Erro ao remover utilizador: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

if (isset($_GET['ok'])) {
    $msg = "Operação realizada com sucesso!";
    $msg_type = "success";
}

$utilizadores = mysqli_query($conn, "SELECT * FROM utilizadores ORDER BY role ASC, nome ASC");
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | Controlo de Acessos</title>
    <link rel="stylesheet" href="css/style_admin.css">
    <style>
        /* Design Diferenciado: Tabela Cibernética */
        .user-table-container {
            background: rgba(10, 10, 10, 0.8);
            border: 1px solid #333;
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            color: #ccc;
            font-size: 0.9rem;
        }

        .user-table th {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--c-neon);
            border-bottom: 2px solid #222;
        }

        .user-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #1a1a1a;
        }

        .user-table tr:hover {
            background: rgba(0, 255, 255, 0.02);
        }

        /* Badges de Role */
        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .badge-master { background: #ffd700; color: #000; } /* Dourado */
        .badge-admin { background: #00ffff; color: #000; }  /* Ciano */
        .badge-user { background: #333; color: #fff; }     /* Cinza */

        /* Ações */
        .btn-action {
            text-decoration: none;
            font-size: 0.75rem;
            margin-right: 10px;
            transition: 0.3s;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            font-family: inherit;
        }
        .promote { color: #00ff00; }
        .demote { color: #ffae00; }
        .ban { color: #ff4d4d; }
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 25px; font-weight: bold; font-size: 0.9rem; }
        .alert.success { background: rgba(0,255,136,0.1); color: #00ff88; border: 1px solid rgba(0,255,136,0.3); }
        .alert.error { background: rgba(255,77,77,0.1); color: #ff4d4d; border: 1px solid rgba(255,77,77,0.3); }
    </style>
</head>
<body class="admin-body">

    <aside class="sidebar-master">
        <div class="sidebar-brand">K-UNIVERSE HQ</div>
        <nav class="sidebar-nav">
            <a href="painel_adminMaster.php" class="nav-link"> Dashboard</a>
            <a href="gerir_produtos.php" class="nav-link"> Inventário</a>
            <a href="gerir_utilizadores.php" class="nav-link active">Utilizadores</a>
            <div class="nav-divider"></div>
            <a href="logout.php" class="nav-link logout-link"> Logout</a>
        </nav>
    </aside>

    <main class="admin-main">
        <header class="main-header">
            <h1>👥 Gestão de Utilizadores</h1>
            <p style="color: #666;">Controlo total sobre cargos e acessos à plataforma.</p>
        </header>

        <?php if ($msg): ?>
            <div class="alert <?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <div class="user-table-container">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>Ações de Controlo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($u = mysqli_fetch_assoc($utilizadores)): ?>
                    <tr>
                        <td style="color: #fff; font-weight: 600;"><?php echo $u['nome']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td>
                            <?php 
                            if($u['role'] == 1) echo '<span class="badge badge-master">MASTER</span>';
                            elseif($u['role'] == 2) echo '<span class="badge badge-admin">ADMIN</span>';
                            else echo '<span class="badge badge-user">CLIENTE</span>';
                            ?>
                        </td>
                        <td>
                            <?php if($u['id_utilizador'] != $_SESSION['user_id']): // Não se auto-editar ?>
                                
                                <?php if($u['role'] != 2): ?>
                                    <form method="POST" style="display:inline-block; margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int) $u['id_utilizador']; ?>">
                                        <input type="hidden" name="new_role" value="2">
                                        <button type="submit" name="btn_update_role" class="btn-action promote">Tornar Admin</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display:inline-block; margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int) $u['id_utilizador']; ?>">
                                        <input type="hidden" name="new_role" value="3">
                                        <button type="submit" name="btn_update_role" class="btn-action demote">Remover Admin</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('Expulsar este utilizador?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="delete_user_id" value="<?php echo (int) $u['id_utilizador']; ?>">
                                    <button type="submit" name="btn_delete_user" class="btn-action ban">Banir</button>
                                </form>
                            
                            <?php else: ?>
                                <span style="color: #444; font-style: italic;">(Tu)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>