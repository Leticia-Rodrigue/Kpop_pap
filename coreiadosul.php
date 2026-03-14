<?php include 'menu.php'; ?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Coreia do Sul</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style_coreia.css">
</head>
<body>

<canvas id="particles"></canvas>
<div class="cursor-dot" id="cursorDot"></div>
<div class="cursor-ring" id="cursorRing"></div>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <span class="hero-flag">&#x1F1F0;&#x1F1F7;</span>
        <h1 class="hero-title">COREIA<br>DO SUL</h1>
        <p class="hero-sub">O país que conquistou o mundo — da tradição milenar ao fenómeno global que é a Onda Coreana.</p>
    </div>

</section>

<!-- STATS -->
<div class="stats-bar">
    <div class="stat-item">
        <div class="stat-num" data-target="52">0</div>
        <div class="stat-label">Milhões de habitantes</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="100210">0</div>
        <div class="stat-label">km² de área</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="5000">0</div>
        <div class="stat-label">Anos de história</div>
    </div>
    <div class="stat-item">
        <div class="stat-num" data-target="1">0</div>
        <div class="stat-label">Das maiores economias mundiais</div>
    </div>
</div>

<hr class="neon-divider">

<!-- SOBRE A COREIA -->
<section class="section">
    <div class="sobre-split">
        <div data-aos="fade-right">
            <span class="tag">&#x1F30F; Descobrir</span>
            <h2 class="sec-title">A terra do <span class="glow">Manhã Calmo</span></h2>
            <p class="sec-text">A Coreia do Sul, oficialmente República da Coreia, é uma nação do Leste Asiático na metade sul da Península Coreana. Com uma história que remonta a mais de 5.000 anos, o país foi moldado por dinastias poderosas, invasões, guerras e uma das recuperações económicas mais impressionantes da história moderna — o chamado "Milagre do Rio Han".</p>
            <p class="sec-text" style="margin-top:14px;">Hoje é uma potência tecnológica, cultural e económica — lar da Samsung, Hyundai, BTS e de uma das gastronomias mais celebradas do mundo. A Coreia do Sul é sinónimo de inovação sem perder as raízes.</p>
        </div>
        <div class="sobre-flag-wrap" data-aos="zoom-in">
            <div class="sobre-flag-img">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/09/Flag_of_South_Korea.svg" alt="Bandeira da Coreia do Sul">
            </div>
            <p class="sobre-flag-label">Taegukgi — Bandeira da Coreia do Sul</p>
            <p class="sobre-flag-desc">O fundo branco representa paz. O Taeguk central simboliza equilíbrio entre forças opostas. Os quatro trigramas representam o céu, a água, a terra e o fogo.</p>
        </div>
    </div>
</section>

<hr class="neon-divider">

<!-- HISTÓRIA: LINHA DO TEMPO -->
<section class="section">
    <span class="tag" data-aos="fade-right">&#x1F4DC; História</span>
    <h2 class="sec-title" data-aos="fade-up">5.000 anos de <span class="glow">história</span></h2>

    <div class="tl-wrap">

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">2333 a.C.</div>
            </div>
            <div class="tl-center"><div class="tl-circle pink"></div></div>
            <div class="tl-right">
                <h3>Fundação de Gojoseon</h3>
                <p>Dangun funda o primeiro reino coreano. Início de uma civilização que duraria milénios e que ainda hoje molda a identidade coreana.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">Séc. VII</div>
            </div>
            <div class="tl-center"><div class="tl-circle cyan"></div></div>
            <div class="tl-right">
                <h3>Reino de Silla Unificado</h3>
                <p>Primeira unificação da Península Coreana. Florescimento do budismo, das artes e da arquitetura — os templos desta era sobrevivem até hoje.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">1392</div>
            </div>
            <div class="tl-center"><div class="tl-circle pink"></div></div>
            <div class="tl-right">
                <h3>Dinastia Joseon</h3>
                <p>500 anos de domínio confuciano. Criação do Hangul — o alfabeto coreano — por ordem do Rei Sejong em 1443. Construção dos grandes palácios de Seoul.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">1910–1945</div>
            </div>
            <div class="tl-center"><div class="tl-circle cyan"></div></div>
            <div class="tl-right">
                <h3>Ocupação Japonesa</h3>
                <p>35 anos de colonização marcados pela resistência cultural coreana. A língua, a cultura e a identidade foram preservadas apesar da repressão.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">1950–1953</div>
            </div>
            <div class="tl-center"><div class="tl-circle pink"></div></div>
            <div class="tl-right">
                <h3>Guerra da Coreia</h3>
                <p>Conflito devastador que divide a península a 38º Paralelo. A Coreia do Sul começa a reconstrução do zero — o PIB per capita era inferior a muitos países africanos.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">1960s–80s</div>
            </div>
            <div class="tl-center"><div class="tl-circle cyan"></div></div>
            <div class="tl-right">
                <h3>Milagre do Rio Han</h3>
                <p>Em apenas 30 anos, a Coreia do Sul passa de país devastado a potência industrial. Surgem a Samsung, a Hyundai e a LG. O crescimento anual chegou a 10%.</p>
            </div>
        </div>

        <div class="tl-row" data-aos="fade-up">
            <div class="tl-left">
                <div class="tl-year-box">1990s–Hoje</div>
            </div>
            <div class="tl-center"><div class="tl-circle pink"></div></div>
            <div class="tl-right">
                <h3>Hallyu — A Onda Coreana</h3>
                <p>K-Pop, K-Drama, cinema e gastronomia conquistam o planeta. Em 2020, Parasite vence o Óscar de Melhor Filme. BTS torna-se o grupo mais ouvido do mundo.</p>
            </div>
        </div>

    </div>
</section>

<hr class="neon-divider">

<!-- GASTRONOMIA -->
<section class="section">
    <span class="tag" data-aos="fade-right">&#x1F35C; Gastronomia</span>
    <h2 class="sec-title" data-aos="fade-up">Sabores que <span class="glow">conquistaram o mundo</span></h2>

    <div class="feature-split" data-aos="fade-up">
        <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=800&q=80" alt="Gastronomia coreana" class="feature-img">
        <div class="feature-text">
            <div class="feature-list">
                <div class="fl-item">
                    <span class="fl-icon">&#x1F96C;</span>
                    <div>
                        <strong>Kimchi</strong>
                        <p>Couve fermentada com especiarias — o prato mais icónico da Coreia. Servido em todas as refeições, é um probiótico natural com mais de 2.000 anos de história.</p>
                    </div>
                </div>
                <div class="fl-item">
                    <span class="fl-icon">&#x1F356;</span>
                    <div>
                        <strong>Korean BBQ</strong>
                        <p>Carne grelhada na mesa, envolta em folhas de alface com pasta de soja. Uma experiência social tanto quanto gastronómica — incontornável em qualquer visita.</p>
                    </div>
                </div>
                <div class="fl-item">
                    <span class="fl-icon">&#x1F35B;</span>
                    <div>
                        <strong>Bibimbap</strong>
                        <p>Arroz misturado com vegetais, ovo, carne e pasta gochujang. Um dos pratos mais equilibrados e coloridos da culinária asiática.</p>
                    </div>
                </div>
                <div class="fl-item">
                    <span class="fl-icon">&#x1F9C6;</span>
                    <div>
                        <strong>Tteokbokki</strong>
                        <p>Bolos de arroz em molho picante. O snack de rua mais popular da Coreia — vendido em pojangmacha (barracas de rua) por todo o país.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<hr class="neon-divider">

<!-- K-DRAMA & CINEMA -->
<section class="section">
    <span class="tag" data-aos="fade-right">&#x1F3AC; Entretenimento</span>
    <h2 class="sec-title" data-aos="fade-up">K-Drama & Cinema — <span class="glow">arte global</span></h2>

    <div class="feature-split reverse" data-aos="fade-up">
        <img src="https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800&q=80" alt="Cinema" class="feature-img">
        <div class="feature-text">
            <p class="sec-text">O entretenimento coreano passou de fenómeno regional a força cultural global em menos de 20 anos. Com narrativas originais e produções de altíssima qualidade, conquistou audiências em mais de 190 países.</p>

            <div class="highlight-row" style="margin-top: 28px;">
                <div class="hl-box">
                    <div class="hl-num glow-pink">2020</div>
                    <div class="hl-label">Parasite vence o Óscar de Melhor Filme — 1.º filme não-anglófono a fazê-lo</div>
                </div>
                <div class="hl-box">
                    <div class="hl-num" style="color:var(--cyan);text-shadow:0 0 20px rgba(0,212,255,0.4);">111M</div>
                    <div class="hl-label">Casas assistiram a Squid Game na Netflix no primeiro mês</div>
                </div>
                <div class="hl-box">
                    <div class="hl-num glow-pink">1992</div>
                    <div class="hl-label">Seo Taiji and Boys lança o primeiro hit de K-Pop moderno</div>
                </div>
            </div>

            <p class="sec-text" style="margin-top:24px;">Os K-Dramas destacam-se pela intensidade emocional, cenários deslumbrantes e histórias de amor complexas. Plataformas como Netflix e Disney+ investem centenas de milhões em produções coreanas anualmente.</p>
        </div>
    </div>
</section>

<hr class="neon-divider">

<!-- TECNOLOGIA -->
<section class="section">
    <span class="tag" data-aos="fade-right">&#x1F4BB; Tecnologia</span>
    <h2 class="sec-title" data-aos="fade-up">Uma das nações mais <span class="glow">tecnológicas do mundo</span></h2>

    <p class="sec-text" data-aos="fade-up">A Coreia do Sul lidera rankings mundiais de velocidade de internet, penetração de fibra óptica e investimento em I&D. É lar de algumas das maiores empresas tecnológicas do planeta.</p>

    <div class="tech-grid" data-aos="fade-up" style="margin-top: 40px;">
        <div class="tech-item">
            <div class="tech-logo">&#x1F4F1;</div>
            <strong>Samsung</strong>
            <p>Líder mundial em semicondutores e ecrãs. Fabrica chips para a Apple, Google e praticamente todos os fabricantes de smartphones do mundo.</p>
        </div>
        <div class="tech-item">
            <div class="tech-logo">&#x1F697;</div>
            <strong>Hyundai & Kia</strong>
            <p>Entre os maiores fabricantes automóveis do mundo. Líderes na transição para veículos elétricos com modelos como o Ioniq 6.</p>
        </div>
        <div class="tech-item">
            <div class="tech-logo">&#x1F4FA;</div>
            <strong>LG Electronics</strong>
            <p>Pioneira em televisores OLED e eletrodomésticos inteligentes. Presente em mais de 60 países com produtos de referência.</p>
        </div>
        <div class="tech-item">
            <div class="tech-logo">&#x1F310;</div>
            <strong>Internet</strong>
            <p>A internet mais rápida do mundo com velocidades médias acima de 200 Mbps. 99% da população tem acesso a banda larga.</p>
        </div>
        <div class="tech-item">
            <div class="tech-logo">&#x1F916;</div>
            <strong>Inteligência Artificial</strong>
            <p>Investimento estatal massivo em IA, robótica e biotecnologia. Seoul é considerada uma das cidades mais "smart" do planeta.</p>
        </div>
        <div class="tech-item">
            <div class="tech-logo">&#x1F3AE;</div>
            <strong>Gaming & Esports</strong>
            <p>Berço do esports competitivo mundial. O StarCraft é desporto nacional. Jogadores coreanos dominam torneios internacionais há duas décadas.</p>
        </div>
    </div>
</section>

<hr class="neon-divider">

<!-- GALERIA -->
<section class="section">
    <span class="tag" data-aos="fade-right">&#x1F4F8; Galeria</span>
    <h2 class="sec-title" data-aos="fade-up">Imagens da <span class="glow">Coreia do Sul</span></h2>
    <div class="masonry">
        <div class="masonry-item" data-aos="fade-up">
            <img src="https://images.unsplash.com/photo-1543873780-730f01e0c87a?w=700&q=80" alt="Seoul">
        </div>
        <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
            <img src="https://images.unsplash.com/photo-1583394293214-0b3a91c2a4b0?w=700&q=80" alt="Hanbok">
        </div>
        <div class="masonry-item" data-aos="fade-up" data-aos-delay="200">
            <img src="https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=700&q=80" alt="Templo">
        </div>
        <div class="masonry-item" data-aos="fade-up">
            <img src="https://images.unsplash.com/photo-1601621915196-2621bfb0cd6e?w=700&q=80" alt="Seoul night">
        </div>
        <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
            <img src="https://images.unsplash.com/photo-1549693578-d683be217e58?w=700&q=80" alt="Busan">
        </div>
        <div class="masonry-item" data-aos="fade-up" data-aos-delay="200">
            <img src="https://images.unsplash.com/photo-1528360983277-13d401cdc186?w=700&q=80" alt="Comida">
        </div>
    </div>
</section>

<hr class="neon-divider">

<!-- BANDEIRA -->
<div class="flag-hero">
    <div class="flag-anim" data-aos="zoom-in">
        <img src="https://upload.wikimedia.org/wikipedia/commons/0/09/Flag_of_South_Korea.svg" alt="Bandeira da Coreia do Sul">
    </div>
    <div data-aos="fade-left">
        <span class="tag">Símbolo Nacional</span>
        <h2 class="sec-title">Taegukgi — <span class="glow">a bandeira</span></h2>
        <p class="sec-text">O fundo branco representa paz e pureza. O círculo central Taeguk simboliza o equilíbrio entre forças opostas — yin e yang. Os quatro trigramas negros representam o céu, a água, a terra e o fogo.</p>
    </div>
</div>

<footer class="footer">
    <p>&copy; 2026 K-Pop Universe &mdash; Projeto Final TGPSI</p>
</footer>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ duration: 900, once: true, offset: 80 });

const dot  = document.getElementById('cursorDot');
const ring = document.getElementById('cursorRing');
document.addEventListener('mousemove', e => {
    dot.style.left  = e.clientX + 'px';
    dot.style.top   = e.clientY + 'px';
    ring.style.left = e.clientX + 'px';
    ring.style.top  = e.clientY + 'px';
});
document.querySelectorAll('a,button,.masonry-item,.tech-item').forEach(el => {
    el.addEventListener('mouseenter', () => { ring.style.transform = 'translate(-50%,-50%) scale(2)'; ring.style.borderColor = 'rgba(255,0,128,0.6)'; });
    el.addEventListener('mouseleave', () => { ring.style.transform = 'translate(-50%,-50%) scale(1)'; ring.style.borderColor = 'rgba(0,212,255,0.4)'; });
});

const canvas = document.getElementById('particles');
const ctx    = canvas.getContext('2d');
canvas.width  = window.innerWidth;
canvas.height = window.innerHeight;
window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; });
const particles = Array.from({length: 60}, () => ({
    x: Math.random() * canvas.width, y: Math.random() * canvas.height,
    r: Math.random() * 1.5 + 0.3,
    dx: (Math.random() - 0.5) * 0.3, dy: (Math.random() - 0.5) * 0.3,
    color: Math.random() > 0.5 ? 'rgba(0,212,255,' : 'rgba(255,0,128,'
}));
function animParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => {
        p.x += p.dx; p.y += p.dy;
        if (p.x < 0 || p.x > canvas.width)  p.dx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.dy *= -1;
        ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = p.color + '0.6)'; ctx.fill();
    });
    requestAnimationFrame(animParticles);
}
animParticles();

function animateCounter(el, target, duration) {
    let start = 0;
    const step = target / (duration / 16);
    const timer = setInterval(() => {
        start += step;
        if (start >= target) { el.textContent = target.toLocaleString(); clearInterval(timer); return; }
        el.textContent = Math.floor(start).toLocaleString();
    }, 16);
}
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
        if (e.isIntersecting) { animateCounter(e.target, parseInt(e.target.dataset.target), 1500); observer.unobserve(e.target); }
    });
}, { threshold: 0.5 });
document.querySelectorAll('.stat-num').forEach(el => observer.observe(el));
</script>
</body>
</html>