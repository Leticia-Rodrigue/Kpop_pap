<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'bd_connection.php'; 
?>

<style>
    /* 1. CONTAINER DO UTILIZADOR */
    .user-info {
        position: relative;
        display: flex;
        align-items: center;
    }

    /* 2. O BOTÃO DO NOME (O GATILHO) */
    .user-trigger {
        background: rgba(255, 255, 255, 0.05);
        padding: 8px 15px;
        border-radius: 50px;
        border: 1px solid rgba(0, 255, 255, 0.3);
        cursor: pointer;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .user-trigger:hover {
        background: rgba(0, 255, 255, 0.1);
        border-color: #00ffff;
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    .user-trigger b { color: #00ffff; }

    /* 3. O SUB-MENU DIFERENCIADO (O CARD) */
    .user-card-menu {
        visibility: hidden;
        opacity: 0;
        position: absolute;
        top: 55px;
        right: 0;
        width: 240px;
        background: rgba(15, 15, 15, 0.95);
        backdrop-filter: blur(15px);
        border: 1px solid #333;
        border-radius: 20px;
        padding: 20px;
        transform: translateY(10px);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        z-index: 9999;
        box-shadow: 0 15px 35px rgba(0,0,0,0.8);
    }

    .user-info:hover .user-card-menu {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    /* 4. CONTEÚDO DENTRO DO CARD */
    .user-card-header {
        border-bottom: 1px solid #222;
        padding-bottom: 15px;
        margin-bottom: 15px;
        text-align: center;
    }

    .user-card-header span {
        font-size: 0.7rem;
        color: #666;
        text-transform: uppercase;
        display: block;
    }

    .user-card-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .user-card-links a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        color: #eee;
        text-decoration: none;
        font-size: 0.85rem;
        border-radius: 10px;
        transition: 0.2s;
        background: rgba(255,255,255,0.02);
    }

    .user-card-links a:hover {
        background: rgba(0, 255, 255, 0.1);
        color: #00ffff;
        transform: translateX(5px);
    }

    .logout-btn {
        margin-top: 10px;
        color: #ff4d4d !important;
        background: rgba(255, 77, 77, 0.05) !important;
    }

    .logout-btn:hover {
        background: #ff4d4d !important;
        color: #fff !important;
    }
</style>

<nav class="navbar" id="mainNavbar">
    <div class="navbar-container">
        <div class="logo">
            <a href="index.php" class="logo-link">
                <img src="img/kpo_logo_(1).png" alt="Logo" class="nav-logo-img">
            </a>
        </div>
        
        <ul class="nav-links">
            <li><a href="index.php">INÍCIO</a></li>
            <li><a href="grupos.php">GRUPOS</a></li>
            <li><a href="agenda.php">AGENDA</a></li>
            <li><a href="produtos.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'produtos.php') ? 'active' : ''; ?>">LOJA</a></li>
            <li><a href="contactos.php">CONTATOS</a></li>
        </ul>

        <div class="nav-actions">
            <?php 
            $paginaAtual = basename($_SERVER['PHP_SELF']);
            if ($paginaAtual == 'produtos.php'): 
            ?>
                <button class="btn-cart-trigger" onclick="toggleCart()">🛒 CARRINHO</button>
            <?php endif; ?>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-info">
                    <div class="user-trigger">
                        <span>Olá, <b><?php echo htmlspecialchars($_SESSION['username']); ?></b></span>
                        <small>▼</small>
                    </div>

                    <div class="user-card-menu">
                        <div class="user-card-header">
                            <span>Sessão Ativa</span>
                            <strong style="color: #fff;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                        </div>

                        <div class="user-card-links">
                            <a href="perfil.php">Editar Conta</a>
                            
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 1 || $_SESSION['role'] == 2)): ?>
                                <a href="admin_encomendas.php">Encomendas</a>
                            <?php else: ?>
                                <a href="compras.php">Minhas Compras</a>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 1 || $_SESSION['role'] == 2)): ?>
                                <a href="<?php echo ($_SESSION['role'] == 1) ? 'painel_adminMaster.php' : 'admin_painel.php'; ?>" style="border: 1px solid #00ffff55;">
                                    Painel <?php echo ($_SESSION['role'] == 1) ? "Master" : "Admin"; ?>
                                </a>
                                <a href="compras.php">Editar Site</a>
                            <?php endif; ?>

                            <a href="logout.php" class="logout-btn">Logout</a>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($paginaAtual != 'registar.php'): ?>
                <a href="registar.php" class="btn-login">Login/Registar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>