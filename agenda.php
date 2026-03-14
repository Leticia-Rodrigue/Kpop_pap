<?php 
require 'textos.php';
require 'bd_connection.php';

$query_news = "SELECT * FROM noticias ORDER BY id_noticia DESC";
$res_news = mysqli_query($conn, $query_news);

$query_agenda = "SELECT * FROM agenda_shows ORDER BY ordem_data ASC";
$res_agenda = mysqli_query($conn, $query_agenda);

$noticias = [];
if ($res_news) {
    while($n = mysqli_fetch_assoc($res_news)) $noticias[] = $n;
}

$shows = [];
if ($res_agenda) {
    while($a = mysqli_fetch_assoc($res_agenda)) $shows[] = $a;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Notícias e Agenda</title>
    <link rel="stylesheet" href="css/style_agenda.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── CARROSSEL WRAPPER ── */
        .carrossel-wrapper {
            position: relative;
            width: 100%;
            overflow: hidden;
            padding: 10px 0 20px;
        }
        .carrossel-track {
            display: flex;
            gap: 24px;
            transition: transform 0.55s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            will-change: transform;
        }
        .carrossel-item {
            flex: 0 0 calc((100% - 48px) / 3);
            min-width: 0;
        }
        @media (max-width: 1024px) { .carrossel-item { flex: 0 0 calc((100% - 24px) / 2); } }
        @media (max-width: 640px)  { .carrossel-item { flex: 0 0 100%; } }

        /* ── CARD NOTÍCIA REDESIGN ── */
        .card-noticia {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            background: #0d0d0d;
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            height: 420px;
            display: flex;
            flex-direction: column;
        }

        .card-noticia:hover {
            transform: translateY(-10px);
            border-color: rgba(255, 0, 128, 0.5);
            box-shadow:
                0 25px 50px rgba(0,0,0,0.7),
                0 0 40px rgba(255,0,128,0.12),
                inset 0 0 30px rgba(255,0,128,0.03);
        }

        /* Imagem com overlay gradiente */
        .card-noticia-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
            transition: transform 0.6s ease;
        }
        .card-noticia:hover .card-noticia-img {
            transform: scale(1.08);
        }

        .card-noticia-img-wrap {
            overflow: hidden;
            position: relative;
            height: 200px;
            flex-shrink: 0;
        }

        /* Gradiente sobre a imagem */
        .card-noticia-img-wrap::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(to top, #0d0d0d, transparent);
        }

        /* Glowing tag no canto */
        .card-noticia-tag {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(255, 0, 128, 0.9);
            backdrop-filter: blur(10px);
            color: white;
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 50px;
            box-shadow: 0 0 15px rgba(255,0,128,0.5);
            z-index: 2;
        }

        /* Conteúdo */
        .card-noticia-body {
            padding: 20px 22px 22px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-noticia-data {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #ff0080;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-noticia-data::before {
            content: '';
            display: inline-block;
            width: 20px;
            height: 2px;
            background: #ff0080;
            border-radius: 2px;
            box-shadow: 0 0 6px rgba(255,0,128,0.8);
        }

        .card-noticia-titulo {
            font-size: 1rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 10px 0;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-noticia-resumo {
            font-size: 0.8rem;
            color: #666;
            line-height: 1.6;
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 16px;
        }

        /* Botão ler mais */
        .card-noticia-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #ff0080;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.3s;
            padding: 10px 18px;
            border: 1px solid rgba(255,0,128,0.3);
            border-radius: 50px;
            background: rgba(255,0,128,0.05);
            align-self: flex-start;
        }
        .card-noticia-btn::after { content: '→'; transition: transform 0.3s; }
        .card-noticia-btn:hover {
            background: #ff0080;
            color: white;
            border-color: #ff0080;
            box-shadow: 0 0 20px rgba(255,0,128,0.4);
        }
        .card-noticia-btn:hover::after { transform: translateX(4px); }

        /* ── AGENDA ── */
        .agenda-section { padding: 20px 0 60px; }
        .agenda-filters {
            display: flex; gap: 12px; margin-bottom: 40px;
            flex-wrap: wrap; justify-content: center;
        }
        .filter-btn {
            padding: 10px 24px; border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.04); color: #888;
            font-family: 'Poppins', sans-serif; font-size: 0.78rem;
            font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
            cursor: pointer; transition: all 0.3s;
        }
        .filter-btn:hover { border-color: #ff0080; color: #ff0080; }
        .filter-btn.active { background: #ff0080; border-color: #ff0080; color: white; box-shadow: 0 0 20px rgba(255,0,128,0.4); }
        .filter-btn[data-filter="BILHETES"].active  { background: #00c9ff; border-color: #00c9ff; box-shadow: 0 0 20px rgba(0,201,255,0.4); }
        .filter-btn[data-filter="FINALIZADO"].active { background: #444; border-color: #666; box-shadow: none; color: #aaa; }

        .shows-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }

        .show-card {
            background: rgba(15,15,15,0.9); border: 1px solid rgba(255,255,255,0.06);
            border-radius: 20px; overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        .show-card:hover { transform: translateY(-6px); border-color: rgba(255,0,128,0.4); box-shadow: 0 20px 40px rgba(0,0,0,0.6), 0 0 30px rgba(255,0,128,0.1); }
        .show-card.hidden { display: none; }
        .show-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 20px 0 0 20px; }
        .show-card[data-estado="EM BREVE"]::before   { background: linear-gradient(180deg, #ff0080, #ff6ec7); }
        .show-card[data-estado="BILHETES"]::before   { background: linear-gradient(180deg, #00c9ff, #4facfe); }
        .show-card[data-estado="FINALIZADO"]::before { background: #333; }

        .show-card-inner { padding: 24px 24px 24px 28px; }
        .show-card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
        .show-date-text { font-size: 0.8rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: #ff0080; }
        .show-card[data-estado="BILHETES"]   .show-date-text { color: #00c9ff; }
        .show-card[data-estado="FINALIZADO"] .show-date-text { color: #555; }
        .show-badge { font-size: 0.65rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; padding: 5px 12px; border-radius: 50px; }
        .badge-breve      { background: rgba(255,0,128,0.15); color: #ff0080; border: 1px solid rgba(255,0,128,0.3); }
        .badge-bilhetes   { background: rgba(0,201,255,0.15); color: #00c9ff; border: 1px solid rgba(0,201,255,0.3); }
        .badge-finalizado { background: rgba(100,100,100,0.15); color: #666; border: 1px solid rgba(100,100,100,0.2); }
        .show-group { font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0 0 6px 0; line-height: 1.2; }
        .show-location { display: flex; align-items: center; gap: 6px; color: #666; font-size: 0.82rem; margin-bottom: 18px; }
        .show-location::before { content: '📍'; font-size: 0.8rem; }
        .show-countdown { display: flex; gap: 10px; margin-bottom: 18px; }
        .countdown-block { display: flex; flex-direction: column; align-items: center; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); border-radius: 10px; padding: 8px 12px; min-width: 52px; }
        .countdown-num { font-size: 1.3rem; font-weight: 800; color: #fff; line-height: 1; }
        .show-card[data-estado="EM BREVE"]  .countdown-num { color: #ff0080; }
        .show-card[data-estado="BILHETES"]  .countdown-num { color: #00c9ff; }
        .countdown-label { font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px; color: #555; margin-top: 2px; }
        .countdown-passed { color: #444; font-size: 0.8rem; font-style: italic; }
        .show-ticket-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; text-decoration: none; transition: all 0.3s; }
        .btn-tickets-active { background: linear-gradient(45deg, #00c9ff, #4facfe); color: #000; box-shadow: 0 5px 15px rgba(0,201,255,0.3); }
        .btn-tickets-active:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,201,255,0.5); }
        .btn-soon { background: rgba(255,0,128,0.1); color: #ff0080; border: 1px solid rgba(255,0,128,0.3); }
        .btn-ended { background: rgba(80,80,80,0.1); color: #555; border: 1px solid #333; cursor: default; }
        .no-shows { text-align: center; color: #555; padding: 60px 20px; font-size: 0.9rem; }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<main>
<br><br>

<!-- SECÇÃO NOTÍCIAS -->
<section id="noticias" class="section-container">
    <div class="section-title">
        <span class="line"></span>
        <h2>Notícias</h2>
        <span class="line"></span>
    </div>

    <?php if (count($noticias) > 0): ?>
    <div class="carrossel-viewport">
        <button class="nav-btn prev" onclick="moveSlide(-1)">❮</button>
        <button class="nav-btn next" onclick="moveSlide(1)">❯</button>
        <div class="carrossel-wrapper">
            <div class="carrossel-track" id="carrosselTrack">
                <?php foreach($noticias as $n):
                    $data_formatada = date('d M Y', strtotime($n['data_pub']));
                ?>
                <div class="carrossel-item">
                    <div class="card-noticia">
                        <div class="card-noticia-img-wrap">
                            <img class="card-noticia-img"
                                 src="<?php echo htmlspecialchars($n['imagem']); ?>"
                                 alt="<?php echo htmlspecialchars($n['titulo']); ?>"
                                 onerror="this.src='https://via.placeholder.com/400x200?text=Sem+Imagem'">
                            <span class="card-noticia-tag">K-Pop News</span>
                        </div>
                        <div class="card-noticia-body">
                            <span class="card-noticia-data"><?php echo strtoupper($data_formatada); ?></span>
                            <h3 class="card-noticia-titulo"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                            <p class="card-noticia-resumo"><?php echo htmlspecialchars($n['resumo']); ?></p>
                            <a href="<?php echo htmlspecialchars($n['link_url']); ?>" target="_blank" class="card-noticia-btn">Ler Mais</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
        <p style="color:#555; text-align:center; padding: 40px 0;">Brevemente novas notícias...</p>
    <?php endif; ?>
</section>

<!-- SECÇÃO AGENDA -->
<section id="agenda" class="section-container agenda-section">
    <div class="section-title">
        <span class="line"></span>
        <h2>Agenda de Shows 2026</h2>
        <span class="line"></span>
    </div>

    <?php if (count($shows) > 0): ?>
    <div class="agenda-filters">
        <button class="filter-btn active" data-filter="TODOS">Todos</button>
        <button class="filter-btn" data-filter="EM BREVE">Em Breve</button>
        <button class="filter-btn" data-filter="BILHETES">Bilhetes</button>
        <button class="filter-btn" data-filter="FINALIZADO">Finalizado</button>
    </div>

    <div class="shows-grid" id="showsGrid">
        <?php foreach($shows as $a):
            $estado = $a['estado'];
            switch($estado) {
                case 'BILHETES':   $badge_class = 'badge-bilhetes';  break;
                case 'FINALIZADO': $badge_class = 'badge-finalizado'; break;
                default:           $badge_class = 'badge-breve';
            }
        ?>
        <div class="show-card" data-estado="<?php echo htmlspecialchars($estado); ?>">
            <div class="show-card-inner">
                <div class="show-card-top">
                    <span class="show-date-text"><?php echo htmlspecialchars($a['data_evento']); ?></span>
                    <span class="show-badge <?php echo $badge_class; ?>"><?php echo $estado; ?></span>
                </div>
                <h3 class="show-group"><?php echo htmlspecialchars($a['grupo_evento']); ?></h3>
                <div class="show-location"><?php echo htmlspecialchars($a['localizacao']); ?></div>

                <?php if ($estado !== 'FINALIZADO'): ?>
                <div class="show-countdown" data-date="<?php echo $a['ordem_data']; ?>">
                    <div class="countdown-block"><span class="countdown-num" data-unit="d">--</span><span class="countdown-label">dias</span></div>
                    <div class="countdown-block"><span class="countdown-num" data-unit="h">--</span><span class="countdown-label">horas</span></div>
                    <div class="countdown-block"><span class="countdown-num" data-unit="m">--</span><span class="countdown-label">min</span></div>
                    <div class="countdown-block"><span class="countdown-num" data-unit="s">--</span><span class="countdown-label">seg</span></div>
                </div>
                <?php endif; ?>

                <?php if ($estado === 'BILHETES' && !empty($a['link_bilhetes'])): ?>
                    <a href="<?php echo htmlspecialchars($a['link_bilhetes']); ?>" target="_blank" class="show-ticket-btn btn-tickets-active">🎟 Comprar Bilhetes</a>
                <?php elseif ($estado === 'EM BREVE'): ?>
                    <span class="show-ticket-btn btn-soon">🔔 Em Breve</span>
                <?php else: ?>
                    <span class="show-ticket-btn btn-ended">✓ Evento Terminado</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <p class="no-shows">Nenhum evento agendado de momento.</p>
    <?php endif; ?>
</section>
</main>

<footer>
    <p>&copy; 2026 K-Pop Universe — O teu portal oficial.</p>
</footer>

<script>
// ── CARROSSEL ──
(function() {
    const track = document.getElementById('carrosselTrack');
    if (!track) return;
    const items = track.querySelectorAll('.carrossel-item');
    let current = 0;

    function getPerPage() {
        if (window.innerWidth < 640)  return 1;
        if (window.innerWidth < 1024) return 2;
        return 3;
    }
    function maxIndex() { return Math.max(0, items.length - getPerPage()); }
    function goTo(i) {
        current = Math.max(0, Math.min(i, maxIndex()));
        track.style.transform = `translateX(${-current * (items[0].offsetWidth + 24)}px)`;
    }
    window.moveSlide = dir => goTo(current + dir);

    let tx = 0;
    track.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend',   e => { if (Math.abs(tx - e.changedTouches[0].clientX) > 50) moveSlide(tx > e.changedTouches[0].clientX ? 1 : -1); });
    window.addEventListener('resize', () => goTo(current));
})();

// ── FILTROS ──
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.show-card').forEach(card => {
            card.classList.toggle('hidden', filter !== 'TODOS' && card.dataset.estado !== filter);
        });
    });
});

// ── COUNTDOWNS ──
function updateCountdowns() {
    document.querySelectorAll('.show-countdown').forEach(el => {
        const diff = new Date(el.dataset.date + 'T00:00:00') - new Date();
        if (diff <= 0) { el.innerHTML = '<span class="countdown-passed">A decorrer / Terminado</span>'; return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000)  / 60000);
        const s = Math.floor((diff % 60000)    / 1000);
        el.querySelector('[data-unit="d"]').textContent = String(d).padStart(2,'0');
        el.querySelector('[data-unit="h"]').textContent = String(h).padStart(2,'0');
        el.querySelector('[data-unit="m"]').textContent = String(m).padStart(2,'0');
        el.querySelector('[data-unit="s"]').textContent = String(s).padStart(2,'0');
    });
}
updateCountdowns();
setInterval(updateCountdowns, 1000);
</script>
</body>
</html>