<?php
include 'bd_connection.php';

// --- SEGURANÇA MÁXIMA ---
// Apenas o Role 1 (Admin Master) pode aceder
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: index.php");
    exit();
}

// LÓGICA: Atualizar cargo de utilizadores
if (isset($_POST['btn_update_role'])) {
    $id_edit = $_POST['id_utilizador'];
    $novo_role = $_POST['novo_role'];
    
    // O Master tem poder total para alterar cargos
    $sql_update = "UPDATE utilizadores SET role_id = '$novo_role' WHERE id_utilizador = '$id_edit'";
    mysqli_query($conn, $sql_update);
    header("Location: painel_adminMaster.php?sucesso=1");
    exit();
}

// LÓGICA: Apagar utilizador
if (isset($_GET['apagar'])) {
    $id_apagar = intval($_GET['apagar']);
    $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT role_id FROM utilizadores WHERE id_utilizador = $id_apagar"));
    if (!$check) {
        die("ERRO: Utilizador com id $id_apagar não encontrado na BD.");
    }
    if ($check['role_id'] == 1) {
        die("ERRO: Não é possível apagar um Master.");
    }
    // Apagar registos dependentes antes de apagar o utilizador
    mysqli_query($conn, "DELETE FROM vendas WHERE id_utilizador = $id_apagar");
    
    $del = mysqli_query($conn, "DELETE FROM utilizadores WHERE id_utilizador = $id_apagar");
    if (!$del) {
        die("ERRO SQL ao apagar: " . mysqli_error($conn));
    }
    header("Location: painel_adminMaster.php?sucesso=1");
    exit();
}

if (isset($_GET['sucesso'])) { $msg = "Operação realizada com sucesso!"; }

// BUSCAR DADOS
$res_users = mysqli_query($conn, "SELECT * FROM utilizadores ORDER BY role_id ASC");
$total_users = mysqli_num_rows($res_users);
$total_prods = mysqli_num_rows(mysqli_query($conn, "SELECT id_produto FROM produtos"));
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | MASTER HQ</title>
    <link rel="stylesheet" href="css/style_adminMaster.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

    <aside class="sidebar-master">
        <div class="sidebar-brand">MASTER CONTROL</div>
        
        <nav class="sidebar-nav">
            <a href="painel_master.php" class="nav-link active">Dashboard Global</a>
            <a href="gerir_grupos.php" class="nav-link">Adicionar Grupos</a>
            <a href="gerir_produtos.php" class="nav-link">Adicionar Produtos</a>
            <a href="gerir_noticias.php" class="nav-link">Adicionar Notícias & Agenda</a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-link">Ver Site</a>
        </nav>
    </aside>

    <main class="admin-main">
        
        <header class="main-header">
            <h1>Painel Master</h1>
            <p>Olá, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>. Controlo total ativado.</p>
        </header>

        <div class="stats-container">
            <div class="stat-card cyan">
                <h3>Membros Totais</h3>
                <div class="stat-value"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card pink">
                <h3>Produtos Loja</h3>
                <div class="stat-value"><?php echo $total_prods; ?></div>
            </div>
            <div class="stat-card pink">
                <h3>Notícias</h3>
                <div class="stat-value"><?php echo mysqli_num_rows(mysqli_query($conn, "SELECT id_noticia FROM noticias")); ?></div>
            </div>
        </div>

        <section class="table-section">
            <h2>Gestão de Hierarquia</h2>
            
            <table class="master-table">
                <thead>
                    <tr>
                        <th>UTILIZADOR</th>
                        <th>EMAIL</th>
                        <th>CARGO</th>
                        <th>AÇÃO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($user = mysqli_fetch_assoc($res_users)): ?>
                    <tr>
                        <td><b><?php echo htmlspecialchars($user['nome']); ?></b></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                            if($user['role_id'] == 1) echo '<span class="badge master">MASTER</span>';
                            elseif($user['role_id'] == 2) echo '<span class="badge admin">ADMIN</span>';
                            else echo '<span class="badge user">USER</span>';
                            ?>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                <form method="POST" class="form-update" style="margin:0;">
                                    <input type="hidden" name="id_utilizador" value="<?php echo $user['id_utilizador']; ?>">
                                    <select name="novo_role">
                                        <option value="1" <?php if($user['role_id']==1) echo 'selected'; ?>>Master</option>
                                        <option value="2" <?php if($user['role_id']==2) echo 'selected'; ?>>Admin</option>
                                        <option value="3" <?php if($user['role_id']==3) echo 'selected'; ?>>User</option>
                                    </select>
                                    <button type="submit" name="btn_update_role">APLICAR</button>
                                </form>
                                <?php if ($user['role_id'] != 1): ?>
                                    <a href="?apagar=<?php echo $user['id_utilizador']; ?>"
                                       onclick="return confirm('Apagar <?php echo htmlspecialchars($user['nome']); ?>? Esta ação não pode ser desfeita.')"
                                       style="padding:6px 14px; border-radius:6px; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:#ff4d4d; border:1px solid #ff4d4d; text-decoration:none; transition:0.3s;"
                                       onmouseover="this.style.background='#ff4d4d';this.style.color='white';"
                                       onmouseout="this.style.background='transparent';this.style.color='#ff4d4d';">
                                        APAGAR
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>