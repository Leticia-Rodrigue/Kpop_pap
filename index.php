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

// bd_connection.php usa $conn (mysqli) — converter para PDO ou usar $conn diretamente
// Verifica qual está disponível
$textos = [];
if (isset($pdo)) {
    $textos = getTextos($pdo, $slug);
} elseif (isset($conn)) {
    // Se getTextos espera PDO mas só existe $conn, usa textos em branco
    // (substitui getTextos por versão mysqli ou deixa vazio)
    $textos = [];
}
if (!is_array($textos)) {
    $textos = [];
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
        .edit-mode-banner {
            max-width: 1180px;
            margin: 30px auto 10px;
            padding: 14px 18px;
            border-radius: 16px;
            border: 1px solid rgba(0, 212, 255, 0.2);
            background: rgba(5, 20, 28, 0.82);
            color: #d7f8ff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            backdrop-filter: blur(10px);
        }
        .edit-mode-banner a {
            color: #00d4ff;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .editable-section-shell {
            position: relative;
            border: 1px dashed rgba(0, 212, 255, 0.18);
            border-radius: 28px;
            padding: 22px;
            margin-top: 20px;
        }
        .section-edit-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 18px;
        }
        .edit-toggle-btn,
        .edit-mode-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(0, 212, 255, 0.22);
            background: rgba(0, 212, 255, 0.08);
            color: #00d4ff;
            text-decoration: none;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
        .edit-feedback {
            max-width: 1100px;
            margin: 0 auto 18px;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 0.86rem;
            font-weight: 600;
        }
        .edit-feedback.success {
            background: rgba(0, 212, 100, 0.08);
            border: 1px solid rgba(0, 212, 100, 0.24);
            color: #00d464;
        }
        .edit-feedback.error {
            background: rgba(255, 68, 102, 0.08);
            border: 1px solid rgba(255, 68, 102, 0.24);
            color: #ff6b88;
        }
        .inline-edit-card {
            width: 100%;
        }
        .edit-title-input,
        .edit-paragraph-input {
            width: 100%;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            color: #fff;
            outline: none;
            font-family: inherit;
        }
        .edit-title-input {
            max-width: 580px;
            padding: 14px 18px;
            text-align: center;
            font-size: clamp(1.5rem, 3vw, 2.3rem);
            font-weight: 800;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.06);
        }
        .edit-paragraph-stack {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .edit-paragraph-input {
            min-height: 110px;
            padding: 16px 18px;
            resize: vertical;
            font-size: 1rem;
            line-height: 1.7;
        }
        .edit-actions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        .edit-save-btn,
        .edit-cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            padding: 12px 18px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            text-decoration: none;
            font-family: inherit;
            cursor: pointer;
        }
        .edit-save-btn {
            border: none;
            background: linear-gradient(45deg, #00c9ff, #00d4ff);
            color: #000;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.25);
        }
        .edit-cancel-btn {
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: transparent;
            color: #bbb;
        }
        @media (max-width: 768px) {
            .edit-mode-banner {
                margin: 20px 16px 10px;
                flex-direction: column;
                align-items: flex-start;
            }
            .editable-section-shell {
                padding: 16px;
            }
            .edit-title-input {
                max-width: 100%;
            }
            .edit-actions {
                flex-direction: column;
            }
            .edit-save-btn,
            .edit-cancel-btn {
                width: 100%;
            }
        }
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

            <?php if ($siteEditMessage !== '' && ($editingSection === 'historia' || (($_GET['saved'] ?? '') === 'historia'))): ?>
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
                        <img src="img/geracoes-kpop.jpg" alt="BTS Group Performance" class="img-neon">
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <section class="featured-groups">
            <h2 class="section-title"><?php echo $textos['titulo_detaques'] ?? ''; ?></h2>
            <div class="grid-container">
                <div class="card" data-aos="fade-up">
                    <div class="card-bg" style="background-image: url('img/bts.png');"></div>
                    <div class="card-overlay"><h3><?php echo $textos['subtitulo_grupo_destaque1'] ?? ''; ?></h3><p><?php echo $textos['texto_grupo_destaque1'] ?? ''; ?></p></div>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-bg" style="background-image: url('img/jump1.jpg');"></div>
                    <div class="card-overlay"><h3><?php echo $textos['subtitulo_grupo_destaque2'] ?? ''; ?></h3><p><?php echo $textos['texto_grupo_destaque2'] ?? ''; ?></p></div>
                </div>
                <div class="card" data-aos="fade-up" data-aos-delay="400">
                    <div class="card-bg" style="background-image: url('img/riize.jpg');"></div>
                    <div class="card-overlay"><h3><?php echo $textos['subtitulo_grupo_destaque3'] ?? ''; ?></h3><p><?php echo $textos['texto_grupo_destaque3'] ?? ''; ?></p></div>
                </div>
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
                            if (i === 0) e.target.playVideo();
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
</body>
</html>