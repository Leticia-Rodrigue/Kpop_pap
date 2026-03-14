<?php 
require 'textos.php';
require 'bd_connection.php';

// session já iniciada nos ficheiros incluídos — não repetir aqui

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
</head>
<body>

    <?php include 'menu.php'; ?>
<br>
    <main>
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

        <section class="sobre-section">
            <div class="section-title">
                <span class="line"></span>
                <h2><?php echo $textos['Titulo_Historia_Evolucao'] ?? ''; ?></h2>
                <span class="line"></span>
            </div>
            <div class="sobre-container">
                <div class="sobre-texto">
                    <p><?php echo $textos['texto1_historia'] ?? ''; ?></p>
                    <p><?php echo $textos['texto2_historia'] ?? ''; ?></p>
                    <p><?php echo $textos['texto3_historia'] ?? ''; ?></p>
                </div>
                <div class="sobre-imagem">
                    <img src="img/geracoes-kpop.jpg" alt="BTS Group Performance" class="img-neon">
                </div>
            </div>
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