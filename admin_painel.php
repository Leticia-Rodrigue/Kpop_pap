<?php
session_start();
include 'bd_connection.php';

// SEGURANÇA: Só Admin (Role 2) entra. O Role 1 (Master) também pode se quiseres.
if (!isset($_SESSION['role']) || $_SESSION['role'] != 2) {
    header("Location: index.php");
    exit();
}

// Lógica para contar dados (Resumo de Staff)
$total_prods = mysqli_num_rows(mysqli_query($conn, "SELECT id_produto FROM produtos"));
$total_news = mysqli_num_rows(mysqli_query($conn, "SELECT id_noticia FROM noticias"));
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | Admin Panel</title>
    <link rel="stylesheet" href="css/style_admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="admin-body">

    <aside class="sidebar-master" style="border-right: 1px solid var(--c-neon);">
        <div class="sidebar-brand" style="color: var(--c-neon);">STAFF PANEL</div>
        
        <nav class="sidebar-nav">
            <a href="admin_painel.php" class="nav-link active" style="border-left-color: var(--c-neon); color: var(--c-neon);">Dashboard Staff</a>
            <a href="gerir_grupos.php" class="nav-link">Adicionar Grupos</a>
            <a href="gerir_produtos.php" class="nav-link">Adicionar Produtos</a>
            <a href="gerir_noticias.php" class="nav-link">Adicionar Notícias</a>
            <div class="nav-divider"></div>
            <a href="index.php" class="nav-link">Ver Site</a>
        </nav>
    </aside>

    <main class="admin-main">
        
        <header class="main-header">
            <h1 style="color: var(--c-neon);">Painel de Gestão</h1>
            <p>Bem-vindo de volta, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b>. Estás em modo Staff.</p>
        </header>

        <div class="stats-container">
            <div class="stat-card cyan">
                <h3>Produtos na Loja</h3>
                <div class="stat-value"><?php echo $total_prods; ?></div>
            </div>
            <div class="stat-card pink">
                <h3>Notícias Publicadas</h3>
                <div class="stat-value"><?php echo $total_news; ?></div>
            </div>
            <div class="stat-card green">
                <h3>Estado</h3>
                <div class="stat-value small" style="color: #00ff00;">ONLINE</div>
            </div>
        </div>

        <section class="action-section" style="margin-top: 40px;">
            <h2 style="margin-bottom: 20px;">Acesso Rápido</h2>
            <div style="display: flex; gap: 20px;">
                <a href="gerir_produtos.php" class="btn-primary" style="text-decoration: none; display: inline-block; text-align: center; width: auto; padding: 15px 30px;">
                    GESTÃO DE PRODUTOS
                </a>
                <a href="gerir_noticias.php" class="btn-primary" style="text-decoration: none; display: inline-block; text-align: center; width: auto; padding: 15px 30px; border-color: var(--c-neon); color: var(--c-neon);">
                    CENTRAL DE NOTÍCIAS
                </a>
            </div>
        </section>

        <section class="info-footer" style="margin-top: 60px; padding: 20px; background: rgba(255,255,255,0.02); border-radius: 10px; border: 1px solid rgba(255,255,255,0.05);">
            <p style="font-size: 0.8rem; color: #555;">
                ⚠️ <b>Aviso:</b> Como Administrador Staff, não tens permissão para gerir cargos ou outros utilizadores. Para alterações de sistema, contacta o <b>Admin Master</b>.
            </p>
        </section>
    </main>

</body>
</html>