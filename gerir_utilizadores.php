<?php
session_start();
include 'bd_connection.php';

// 1. SEGURANÇA: Apenas Master (Role 1) costuma gerir utilizadores
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

// 2. LÓGICA: ALTERAR CARGO (Role)
if (isset($_GET['promote'])) {
    $id = intval($_GET['promote']);
    $new_role = intval($_GET['role']);
    mysqli_query($conn, "UPDATE utilizadores SET role = $new_role WHERE id_utilizador = $id");
    header("Location: gerir_utilizadores.php");
}

// 3. LÓGICA: BANIR/REMOVER
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM utilizadores WHERE id_utilizador = $id");
    header("Location: gerir_utilizadores.php");
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
        }
        .promote { color: #00ff00; }
        .demote { color: #ffae00; }
        .ban { color: #ff4d4d; }
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
                                    <a href="?promote=<?php echo $u['id_utilizador']; ?>&role=2" class="btn-action promote">Tornar Admin</a>
                                <?php else: ?>
                                    <a href="?promote=<?php echo $u['id_utilizador']; ?>&role=3" class="btn-action demote">Remover Admin</a>
                                <?php endif; ?>

                                <a href="?delete=<?php echo $u['id_utilizador']; ?>" class="btn-action ban" onclick="return confirm('Expulsar este utilizador?')">Banir</a>
                            
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