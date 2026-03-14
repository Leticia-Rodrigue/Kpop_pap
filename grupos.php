<?php
require_once 'bd_connection.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Corrigi a Query para usar os nomes de colunas que criámos no gerir_grupos.php
// Se a tua coluna se chama 'nome_grupo' e não 'nome', mudei aqui:
$sql = "SELECT * FROM grupos 
        ORDER BY CASE 
            WHEN nome_grupo IN ('BLACKPINK','RIIZE','BTS','NewJeans') THEN 0 
            ELSE 1 
        END, 
        FIELD(nome_grupo,'BLACKPINK','RIIZE','BTS','NewJeans'), 
        RAND()";

$res = mysqli_query($conn, $sql);

// 2. Verificação de erro (Isto evita o Fatal Error e diz-te o que está mal)
if (!$res) {
    die("Erro na Base de Dados: " . mysqli_error($conn));
}

$grupos = [];
while ($row = mysqli_fetch_assoc($res)) {
    $grupos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer"> <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Grupos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style_grupos.css">
    <style>
        .social-box { margin-top: 25px; border-top: 1px solid #333; padding-top: 15px; }
        .social-box h4 { color: #00f2fe; font-size: 0.8rem; margin-bottom: 15px; letter-spacing: 2px; }
        .social-icons-drawer { display: flex; gap: 20px; }
        .social-icons-drawer a { color: #fff; font-size: 1.8rem; transition: 0.3s; }
        .social-icons-drawer a:hover { color: #00f2fe; transform: translateY(-3px); filter: drop-shadow(0 0 5px #00f2fe); }
        .social-icons-drawer a[href="#"], .social-icons-drawer a[href=""] { opacity: 0.2; pointer-events: none; }
        
        /* Garantir que o background do card funciona com fotos da net */
        .card { background-size: cover; background-position: center; transition: 0.5s; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="section-header" style="padding-top: 120px;">
    <div class="section-title" data-aos="zoom-in">
        <span class="line"></span>
        <h1>Grupos</h1>
        <span class="line"></span>
    </div>
</div>

<main class="main-layout">
    <aside class="sidebar-filtros" data-aos="fade-right">
        <div class="card-sidebar">
            <h3>FILTROS</h3>
            <div class="filter-section">
                <h4>Gerações</h4>
                <label class="neon-check">1.ª GERAÇÃO <input type="checkbox" class="filter-input filter-gen" value="1st Gen"><span class="checkmark"></span></label>
                <label class="neon-check">2.ª GERAÇÃO <input type="checkbox" class="filter-input filter-gen" value="2nd Gen"><span class="checkmark"></span></label>
                <label class="neon-check">3.ª GERAÇÃO <input type="checkbox" class="filter-input filter-gen" value="3rd Gen"><span class="checkmark"></span></label>
                <label class="neon-check">4.ª GERAÇÃO <input type="checkbox" class="filter-input filter-gen" value="4th Gen"><span class="checkmark"></span></label>
                <label class="neon-check">5.ª GERAÇÃO <input type="checkbox" class="filter-input filter-gen" value="5th Gen"><span class="checkmark"></span></label>
                <label class="neon-check">SOLISTAS <input type="checkbox" class="filter-input filter-gen" value="Solista"><span class="checkmark"></span></label>
            </div>
            <div class="filter-section">
                <h4>Género</h4>
                <label class="neon-check">MASCULINO <input type="checkbox" class="filter-input filter-gender" value="Masculino"><span class="checkmark"></span></label>
                <label class="neon-check">FEMININO <input type="checkbox" class="filter-input filter-gender" value="Feminino"><span class="checkmark"></span></label>
            </div>
        </div>
    </aside>

    <section class="content-area">
        <div class="grid-container" id="gruposGrid">
            <?php foreach ($grupos as $i => $grupo):
                // AJUSTE DAS VARIÁVEIS PARA O NOME CORRETO DA BD
                $imagem = $grupo['foto_url'] ?: 'img/default.jpg';
                $membros_txt = $grupo['membros'] ? $grupo['membros'] : '—';
                $delay = ($i % 6) * 100 + 100;
            ?>
            <div class="card"
                 data-gen="<?php echo htmlspecialchars($grupo['tag']); ?>"
                 data-gender="<?php echo htmlspecialchars($grupo['genero']); ?>"
                 data-aos="fade-up"
                 data-aos-delay="<?php echo $delay; ?>"
                 style="background-image: url('<?php echo htmlspecialchars($imagem); ?>');">
                
                <div class="card-overlay">
                    <span class="tag"><?php echo htmlspecialchars($grupo['tag']); ?></span>
                    <h3><?php echo htmlspecialchars($grupo['nome_grupo']); ?></h3>
                    <button class="btn-more" onclick="openProfile(
                        '<?php echo addslashes($grupo['nome_grupo']); ?>',
                        '<?php echo addslashes($membros_txt); ?>',
                        '<?php echo addslashes($grupo['empresa'] ?? '—'); ?>',
                        '<?php echo addslashes($grupo['data_debut'] ?? '—'); ?>',
                        '<?php echo addslashes($grupo['descricao'] ?? ''); ?>',
                        '<?php echo addslashes($imagem); ?>',
                        '<?php echo addslashes($grupo['insta_link'] ?? '#'); ?>',
                        '<?php echo addslashes($grupo['yt_link'] ?? '#'); ?>',
                        '<?php echo addslashes($grupo['tt_link'] ?? '#'); ?>'
                    )">Ver Perfil</button>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (empty($grupos)): ?>
                <p style="color:#555; padding:40px; grid-column:1/-1; text-align:center;">Nenhum grupo encontrado na base de dados.</p>
            <?php endif; ?>
        </div>
    </section>
</main>

<div id="profileOverlay" class="profile-overlay" onclick="closeProfile()"></div>
<div id="profileDrawer" class="profile-drawer">
    <div class="drawer-header">
        <h2 id="p-nome">PERFIL</h2>
        <button class="close-btn" onclick="closeProfile()">&times;</button>
    </div>
    <div class="drawer-content">
        <img id="p-img" src="" alt="" style="width:100%; height:250px; object-fit:cover; border-radius:15px; margin-bottom:20px;">
        <div class="info-item"><span>MEMBROS</span> <b id="p-membros"></b></div>
        <div class="info-item"><span>EMPRESA</span> <b id="p-empresa"></b></div>
        <div class="info-item"><span>DEBUT</span> <b id="p-debut"></b></div>
        <div class="description-box">
            <h4>DESCRIÇÃO</h4>
            <p id="p-desc"></p>
        </div>
        <div class="social-box">
            <h4>REDES SOCIAIS</h4>
            <div class="social-icons-drawer">
                <a id="p-insta" href="#" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                <a id="p-yt" href="#" target="_blank"><i class="fa-brands fa-youtube"></i></a>
                <a id="p-tt" href="#" target="_blank"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; <?php echo date("Y"); ?> K-Pop Universe</p>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ once: true });

    function openProfile(nome, membros, empresa, debut, desc, img, insta, yt, tt) {
        document.getElementById('p-nome').innerText    = nome;
        document.getElementById('p-membros').innerText = membros;
        document.getElementById('p-empresa').innerText = empresa;
        document.getElementById('p-debut').innerText   = debut;
        document.getElementById('p-desc').innerText    = desc;
        document.getElementById('p-img').src           = img;
        
        // Ajuste dos links sociais
        const instaBtn = document.getElementById('p-insta');
        const ytBtn = document.getElementById('p-yt');
        const ttBtn = document.getElementById('p-tt');

        instaBtn.href = (insta && insta !== '#') ? insta : '#';
        ytBtn.href = (yt && yt !== '#') ? yt : '#';
        ttBtn.href = (tt && tt !== '#') ? tt : '#';

        document.getElementById('profileDrawer').classList.add('active');
        document.getElementById('profileOverlay').classList.add('active');
    }

    function closeProfile() {
        document.getElementById('profileDrawer').classList.remove('active');
        document.getElementById('profileOverlay').classList.remove('active');
    }

    // Filtros
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.filter-input');
        const cards      = document.querySelectorAll('.card');

        checkboxes.forEach(box => {
            box.addEventListener('change', () => {
                const activeGens    = Array.from(document.querySelectorAll('.filter-gen:checked')).map(cb => cb.value);
                const activeGenders = Array.from(document.querySelectorAll('.filter-gender:checked')).map(cb => cb.value);
                
                cards.forEach(card => {
                    const genMatch    = activeGens.length    === 0 || activeGens.some(g => card.dataset.gen.includes(g));
                    const genderMatch = activeGenders.length === 0 || activeGenders.includes(card.dataset.gender);
                    card.style.display = (genMatch && genderMatch) ? "block" : "none";
                });
                AOS.refresh();
            });
        });
    });
</script>
</body>
</html>