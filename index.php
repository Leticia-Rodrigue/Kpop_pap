<?php 
require 'textos.php';
require 'bd_connection.php';
require_once 'csrf_helper.php';

// session já iniciada nos ficheiros incluídos — não repetir aqui

$canEditSite = isset($_SESSION['role']) && in_array((int) $_SESSION['role'], [1, 2], true);
$isEditMode = $canEditSite && (($_GET['edit_mode'] ?? '') === '1');
$editingSection = $isEditMode ? ($_GET['edit_section'] ?? '') : '';
$siteEditMessage = '';
$siteEditMessageType = 'success';

// Estes slugs identificam os blocos da homepage que ja vivem na tabela conteudos_paginas.
$slug = [
    'Titulo_Historia_Evolucao',
    'texto1_historia',
    'texto2_historia',
    'texto3_historia',
    'titulo_detaques',
    'subtitulo_grupo_destaque1',
    'texto_grupo_destaque1',
    'subtitulo_grupo_destaque2',
    'texto_grupo_destaque2',
    'subtitulo_grupo_destaque3',
    'texto_grupo_destaque3'
];

// A home continua a abrir mesmo que o PDO falhe; nesse caso os textos dinamicos ficam vazios.
$textos = [];
if (isset($pdo) && $pdo instanceof PDO) {
    $textos = getTextos($pdo, $slug);
}
if (!is_array($textos)) {
    $textos = [];
}

// Carregar grupos em destaque da BD
$destaque_grupos = [];
$nomes_destaque = ['BTS', 'BLACKPINK', 'RIIZE'];
$nomes_sql = "'" . implode("','", $nomes_destaque) . "'";
$res_dest = mysqli_query($conn, "SELECT nome_grupo, foto_url FROM grupos WHERE nome_grupo IN ($nomes_sql)");
if ($res_dest) {
    while ($d = mysqli_fetch_assoc($res_dest)) {
        $destaque_grupos[$d['nome_grupo']] = $d['foto_url'];
    }
}

if (!function_exists('indexTextValue')) {
    function indexTextValue(array $textos, string $slug): string
    {
        $value = $textos[$slug] ?? '';
        return is_string($value) ? $value : '';
    }

    function indexTextPlain(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    function indexTextParagraph(string $value): string
    {
        return nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

// Prova de conceito: por agora so a secao da historia entra em modo de edicao inline.
$historySlugs = [
    'Titulo_Historia_Evolucao',
    'texto1_historia',
    'texto2_historia',
    'texto3_historia',
];

$historyValues = [];
foreach ($historySlugs as $historySlug) {
    $historyValues[$historySlug] = indexTextValue($textos, $historySlug);
}

// Guarda os slugs editaveis da historia usando o mesmo mecanismo dinamico do resto da homepage.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_save_history_section'])) {
    if (!$canEditSite) {
        header('Location: index.php');
        exit();
    }

    $isEditMode = true;
    $editingSection = 'historia';

    foreach ($historySlugs as $historySlug) {
        $historyValues[$historySlug] = trim((string) ($_POST[$historySlug] ?? ''));
    }

    if (!is_valid_csrf_token($_POST['csrf_token'] ?? null)) {
        $siteEditMessage = 'Pedido inválido. Atualiza a página e tenta novamente.';
        $siteEditMessageType = 'error';
    } elseif (!isset($pdo) || !($pdo instanceof PDO)) {
        $siteEditMessage = 'A ligação de edição não está disponível neste momento.';
        $siteEditMessageType = 'error';
    } elseif (saveTextos($pdo, $historyValues)) {
        header('Location: index.php?edit_mode=1&saved=historia#historia-section');
        exit();
    } else {
        $siteEditMessage = 'Não foi possível guardar as alterações. Tenta novamente.';
        $siteEditMessageType = 'error';
    }
}

if ($isEditMode && (($_GET['saved'] ?? '') === 'historia')) {
    $siteEditMessage = 'Secção atualizada com sucesso!';
    $siteEditMessageType = 'success';
}

// ── UPLOAD IMAGEM HISTÓRIA ──
if ($canEditSite && isset($_POST['btn_upload_historia'])) {
    if (!empty($_FILES['img_historia']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['img_historia']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $filename = 'geracoes-kpop.' . $ext;
            if (move_uploaded_file($_FILES['img_historia']['tmp_name'], 'img/' . $filename)) {
                $siteEditMessage     = 'Imagem da história atualizada!';
                $siteEditMessageType = 'success';
            } else {
                $siteEditMessage     = 'Erro ao guardar a imagem.';
                $siteEditMessageType = 'error';
            }
        } else {
            $siteEditMessage     = 'Formato inválido. Usa JPG, PNG ou WEBP.';
            $siteEditMessageType = 'error';
        }
    }
}

// ── UPLOAD IMAGEM CARD DESTAQUE ──
if ($canEditSite && isset($_POST['btn_upload_card'])) {
    $nome_grupo = $_POST['nome_grupo_card'] ?? '';
    if (!empty($_FILES['img_card']['name']) && !empty($nome_grupo)) {
        $ext     = strtolower(pathinfo($_FILES['img_card']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            if (!is_dir('img/grupos')) { mkdir('img/grupos', 0755, true); }
            $filename = 'grupo_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nome_grupo)) . '.' . $ext;
            if (move_uploaded_file($_FILES['img_card']['tmp_name'], 'img/grupos/' . $filename)) {
                $url_safe = mysqli_real_escape_string($conn, 'img/grupos/' . $filename);
                $nome_safe = mysqli_real_escape_string($conn, $nome_grupo);
                mysqli_query($conn, "UPDATE grupos SET foto_url='$url_safe' WHERE nome_grupo='$nome_safe'");
                $siteEditMessage     = "Imagem de $nome_grupo atualizada!";
                $siteEditMessageType = 'success';
            } else {
                $siteEditMessage     = 'Erro ao guardar a imagem.';
                $siteEditMessageType = 'error';
            }
        } else {
            $siteEditMessage     = 'Formato inválido.';
            $siteEditMessageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Home</title>
    <link rel="stylesheet" href="css/style_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        .upload-label {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 16px; border-radius: 8px; cursor: pointer;
            border: 1px dashed rgba(0,212,255,0.4); color: #00d4ff;
            font-size: 0.75rem; font-weight: 700; letter-spacing: 1px;
            text-transform: uppercase; transition: all 0.3s; background: rgba(0,212,255,0.05);
        }
        .upload-label:hover { background: rgba(0,212,255,0.12); border-color: rgba(0,212,255,0.7); }
        .upload-btn {
            padding: 8px 16px; border-radius: 8px; border: none;
            background: linear-gradient(45deg, #ff00ff, #00d4ff);
            color: #000; font-weight: 800; font-size: 0.72rem;
            letter-spacing: 1px; text-transform: uppercase; cursor: pointer; transition: 0.3s;
        }
        .upload-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(0,212,255,0.3); }
    </style>
</head>
<body>

    <?php include 'menu.php'; ?>
<br>
    <main>
        <?php if ($isEditMode): ?>
            <div class="edit-mode-banner">
                <span>Modo de edição ativo. Nesta prova de conceito podes editar a secção da história diretamente na home.</span>
                <a href="index.php#historia-section">Sair da edição</a>
            </div>
        <?php endif; ?>

        <div class="video-background-container">
            <div class="video-wrapper active" data-title="BLACKPINK - JUMP">
                <iframe id="yt_0" src="https://www.youtube.com/embed/P169hsXjYQs?autoplay=1&mute=1&loop=1&playlist=P169hsXjYQs&controls=0&showinfo=0&rel=0&enablejsapi=1" frameborder="0" allow="autoplay"></iframe>
            </div>
            <div class="video-wrapper" data-title="JENNIE - Seoul City + ZEN + like JENNIE">
                <iframe id="yt_1" src="https://www.youtube.com/embed/mvnLt4dLwZQ?autoplay=1&mute=1&loop=1&playlist=mvnLt4dLwZQ&controls=0&showinfo=0&rel=0&enablejsapi=1" frameborder="0" allow="autoplay"></iframe>
            </div>
            <div class="video-wrapper" data-title="BTS - DYNAMITE">
                <iframe id="yt_2" src="https://www.youtube.com/embed/gdZLi9oWNZg?autoplay=1&mute=1&loop=1&playlist=gdZLi9oWNZg&controls=0&showinfo=0&rel=0&enablejsapi=1" frameborder="0" allow="autoplay"></iframe>
            </div>
            <div class="video-wrapper" data-title="BLACKPINK - PINK VENOM">
                <iframe id="yt_3" src="https://www.youtube.com/embed/gQlMMD8auMs?autoplay=1&mute=1&loop=1&playlist=gQlMMD8auMs&controls=0&showinfo=0&rel=0&enablejsapi=1" frameborder="0" allow="autoplay"></iframe>
            </div>
            <div class="video-wrapper" data-title="RIIZE - LOVE 119">
                <iframe id="yt_4" src="https://www.youtube.com/embed/e3YTbgramxo?autoplay=1&mute=1&loop=1&playlist=e3YTbgramxo&controls=0&showinfo=0&rel=0&enablejsapi=1" frameborder="0" allow="autoplay"></iframe>
            </div>
        </div>

        <div class="overlay-dark"></div>

        <header class="hero-section">
            <div class="mv-details" data-aos="zoom-in">
                <p class="subtitle" style="color: var(--s-neon); letter-spacing: 5px;">A REPRODUZIR AGORA</p>
                <h1 id="mvTitle" class="main-title fade-in">BLACKPINK - JUMP</h1>
                <a href="grupos.php" style="text-decoration: none;">
                    <button class="btn-primary" style="margin-top:20px; padding: 12px 35px; background: transparent; border: 2px solid var(--p-neon); color: white; border-radius: 50px; cursor: pointer; box-shadow: 0 0 15px var(--p-neon);">
                        EXPLORAR GERAÇÕES
                    </button>
                </a>
            </div>
        </header>

        <section class="sobre-section<?php echo $isEditMode ? ' editable-section-shell' : ''; ?>" id="historia-section">
            <?php if ($isEditMode): ?>
                <div class="section-edit-toolbar">
                    <?php if ($editingSection === 'historia'): ?>
                        <span class="edit-mode-pill">A editar: História</span>
                    <?php else: ?>
                        <a href="index.php?edit_mode=1&edit_section=historia#historia-section" class="edit-toggle-btn">Editar esta secção</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($siteEditMessage !== ''): ?>
                <div class="edit-feedback <?php echo $siteEditMessageType; ?>"><?php echo htmlspecialchars($siteEditMessage); ?></div>
            <?php endif; ?>

            <?php if ($isEditMode && $editingSection === 'historia'): ?>
                <form method="POST" class="inline-edit-card">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="section-title">
                        <span class="line"></span>
                        <input type="text" name="Titulo_Historia_Evolucao" value="<?php echo htmlspecialchars($historyValues['Titulo_Historia_Evolucao'], ENT_QUOTES, 'UTF-8'); ?>" class="edit-title-input" required>
                        <span class="line"></span>
                    </div>
                    <div class="sobre-container">
                        <div class="sobre-texto edit-paragraph-stack">
                            <textarea name="texto1_historia" class="edit-paragraph-input" required><?php echo htmlspecialchars($historyValues['texto1_historia'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <textarea name="texto2_historia" class="edit-paragraph-input" required><?php echo htmlspecialchars($historyValues['texto2_historia'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <textarea name="texto3_historia" class="edit-paragraph-input" required><?php echo htmlspecialchars($historyValues['texto3_historia'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            <div class="edit-actions">
                                <button type="submit" name="btn_save_history_section" class="edit-save-btn">Guardar</button>
                                <a href="index.php?edit_mode=1#historia-section" class="edit-cancel-btn">Cancelar</a>
                            </div>
                        </div>
                        <div class="sobre-imagem">
                            <img src="img/geracoes-kpop.jpg" alt="BTS Group Performance" class="img-neon">
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="section-title">
                    <span class="line"></span>
                    <h2><?php echo indexTextPlain(indexTextValue($textos, 'Titulo_Historia_Evolucao')); ?></h2>
                    <span class="line"></span>
                </div>
                <div class="sobre-container">
                    <div class="sobre-texto">
                        <p><?php echo indexTextParagraph(indexTextValue($textos, 'texto1_historia')); ?></p>
                        <p><?php echo indexTextParagraph(indexTextValue($textos, 'texto2_historia')); ?></p>
                        <p><?php echo indexTextParagraph(indexTextValue($textos, 'texto3_historia')); ?></p>
                    </div>
                    <div class="sobre-imagem">
                        <img src="img/geracoes-kpop.jpg" alt="BTS Group Performance" class="img-neon" id="prevHistoria">
                    </div>
                </div>
                <?php if ($isEditMode): ?>
                <form method="POST" action="index.php?edit_mode=1" enctype="multipart/form-data" style="margin-top:20px; display:flex; gap:10px; align-items:center; justify-content:flex-end;">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="upload-label">
                        <i class="fas fa-upload"></i> Nova imagem da secção
                        <input type="file" name="img_historia" accept="image/*" onchange="previewEdit(this,'prevHistoria')" style="display:none">
                    </label>
                    <button type="submit" name="btn_upload_historia" class="upload-btn">Guardar</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section class="featured-groups">
            <h2 class="section-title"><?php echo indexTextPlain(indexTextValue($textos, 'titulo_detaques')); ?></h2>
            <div class="grid-container">
                <?php
                $destaques_config = [
                    ['nome'=>'BTS',      'fallback'=>'img/bts.png',   'slug1'=>'subtitulo_grupo_destaque1','slug2'=>'texto_grupo_destaque1','delay'=>''],
                    ['nome'=>'BLACKPINK','fallback'=>'img/jump1.jpg', 'slug1'=>'subtitulo_grupo_destaque2','slug2'=>'texto_grupo_destaque2','delay'=>'200'],
                    ['nome'=>'RIIZE',    'fallback'=>'img/riize.jpg', 'slug1'=>'subtitulo_grupo_destaque3','slug2'=>'texto_grupo_destaque3','delay'=>'400'],
                ];
                foreach ($destaques_config as $dc):
                    $dc_img = $destaque_grupos[$dc['nome']] ?? $dc['fallback'];
                    $dc_id  = 'prev' . $dc['nome'];
                ?>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <div class="card" data-aos="fade-up" <?php echo $dc['delay'] ? 'data-aos-delay="'.$dc['delay'].'"' : ''; ?>>
                        <img class="card-bg" id="<?php echo $dc_id; ?>" src="<?php echo htmlspecialchars($dc_img); ?>" referrerpolicy="no-referrer" alt="<?php echo $dc['nome']; ?>" onerror="this.src='<?php echo $dc['fallback']; ?>'">
                        <div class="card-overlay">
                            <h3><?php echo indexTextPlain(indexTextValue($textos, $dc['slug1'])); ?></h3>
                            <p><?php echo indexTextParagraph(indexTextValue($textos, $dc['slug2'])); ?></p>
                        </div>
                    </div>
                    <?php if ($isEditMode): ?>
                    <form method="POST" action="index.php?edit_mode=1" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="nome_grupo_card" value="<?php echo $dc['nome']; ?>">
                        <label class="upload-label" style="flex:1;">
                            <i class="fas fa-upload"></i> <?php echo $dc['nome']; ?>
                            <input type="file" name="img_card" accept="image/*" onchange="previewEdit(this,'<?php echo $dc_id; ?>')" style="display:none">
                        </label>
                        <button type="submit" name="btn_upload_card" class="upload-btn">Guardar</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <p>&copy; 2026 K-Pop Universe</p>
    </footer>

    <!-- Controlo de Som -->
    <div class="sound-control" id="soundControl">
        <button class="btn-mute muted" id="btnMute" onclick="toggleOpen()">🔇</button>
        <div class="sound-inner">
            <input type="range" class="volume-slider" id="volumeSlider"
                   min="0" max="100" value="0"
                   oninput="onVolumeChange(this.value)"
                   style="--vol:0%">
            <span class="volume-pct" id="volumePct">OFF</span>
        </div>
    </div>

    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        const wrappers  = document.querySelectorAll('.video-wrapper');
        const titleEl   = document.getElementById('mvTitle');
        const soundCtrl = document.getElementById('soundControl');
        const btnMute   = document.getElementById('btnMute');
        const slider    = document.getElementById('volumeSlider');
        const pctEl     = document.getElementById('volumePct');
        let current = 0, isMuted = true, isOpen = false, volume = 70;
        let players = [], ytReady = false;

        function onYouTubeIframeAPIReady() {
            ytReady = true;
            wrappers.forEach((w, i) => {
                players[i] = new YT.Player('yt_' + i, {
                    events: {
                        'onReady': (e) => {
                            e.target.mute();
                            e.target.setPlaybackQuality('hd1080');
                            if (i === 0) e.target.playVideo();
                        },
                        'onPlaybackQualityChange': (e) => {
                            // Forçar hd1080 se mudar automaticamente
                            const q = e.target.getPlaybackQuality();
                            if (q !== 'hd1080' && q !== 'highres') {
                                e.target.setPlaybackQuality('hd1080');
                            }
                        }
                    }
                });
            });
        }

        function rotateVideos() {
            const prev = current;
            current = (current + 1) % wrappers.length;
            wrappers[prev].classList.remove('active');
            setTimeout(() => wrappers[current].classList.add('active'), 200);
            titleEl.classList.remove('fade-in');
            titleEl.classList.add('fade-out');
            setTimeout(() => {
                titleEl.innerText = wrappers[current].getAttribute('data-title');
                titleEl.classList.remove('fade-out');
                titleEl.classList.add('fade-in');
            }, 600);
            if (ytReady) {
                try { players[prev].pauseVideo(); } catch(e) {}
                try {
                    players[current].setPlaybackQuality('hd1080');
                    players[current].playVideo();
                    if (!isMuted) { players[current].unMute(); players[current].setVolume(volume); }
                } catch(e) {}
            }
        }
        setInterval(rotateVideos, 15000);

        function toggleOpen() {
            if (!isOpen) {
                isOpen = true;
                soundCtrl.classList.add('open');
            } else {
                toggleMute();
            }
        }

        document.addEventListener('click', (e) => {
            if (isOpen && !soundCtrl.contains(e.target)) {
                isOpen = false;
                soundCtrl.classList.remove('open');
            }
        });

        function toggleMute() {
            isMuted = !isMuted;
            if (isMuted) {
                volume = parseInt(slider.value) || 70;
                applyToPlayer('mute');
                slider.value = 0; updateSliderUI(0);
                btnMute.textContent = '🔇'; btnMute.classList.add('muted');
            } else {
                applyToPlayer('unmute'); applyToPlayer('volume', volume);
                slider.value = volume; updateSliderUI(volume);
                btnMute.textContent = volume > 50 ? '🔊' : '🔉';
                btnMute.classList.remove('muted');
            }
        }

        function onVolumeChange(val) {
            val = parseInt(val); volume = val; updateSliderUI(val);
            if (val === 0) {
                isMuted = true; applyToPlayer('mute');
                btnMute.textContent = '🔇'; btnMute.classList.add('muted');
            } else {
                isMuted = false; applyToPlayer('unmute'); applyToPlayer('volume', val);
                btnMute.textContent = val > 50 ? '🔊' : '🔉';
                btnMute.classList.remove('muted');
            }
        }

        function updateSliderUI(val) {
            slider.style.setProperty('--vol', val + '%');
            pctEl.textContent = val === 0 ? 'OFF' : val + '%';
        }

        function applyToPlayer(action, val) {
            if (!ytReady) return;
            try {
                if (action === 'mute')   players[current].mute();
                if (action === 'unmute') players[current].unMute();
                if (action === 'volume') players[current].setVolume(val);
            } catch(e) {}
        }
    </script>
    <script>
    function previewEdit(input, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { document.getElementById(imgId).src = e.target.result; };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>