<?php
require 'Config.php';
// Iniciar sessão para validar o captcha depois
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Contactos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_contactos.css">
    <style>
        .msg-feedback { padding: 14px 20px; border-radius: 10px; font-size: 0.88rem; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .msg-sucesso  { background: rgba(0,212,100,0.1); border: 1px solid rgba(0,212,100,0.3); color: #00d464; }
        .msg-erro     { background: rgba(255,68,102,0.1); border: 1px solid rgba(255,68,102,0.3); color: #ff4466; }

        /* MODAL CAPTCHA */
        .captcha-modal-overlay {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; z-index: 1000;
            background: rgba(0,0,0,0.8); backdrop-filter: blur(8px);
            justify-content: center; align-items: center;
        }
        .captcha-modal-overlay.active { display: flex; }

        .captcha-modal {
            background: #0d0d0d;
            border: 1px solid rgba(0,212,255,0.3);
            border-radius: 20px;
            padding: 40px;
            width: 100%; max-width: 380px;
            box-shadow: 0 0 40px rgba(0,212,255,0.2);
            animation: modalIn 0.35s cubic-bezier(0.175,0.885,0.32,1.275);
            position: relative;
            text-align: center;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.85) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .captcha-modal h2 { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 10px; text-transform: uppercase; }
        .captcha-modal p { font-size: 0.85rem; color: #888; margin-bottom: 25px; }

        .captcha-math-wrap {
            display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 20px;
        }
        .captcha-math {
            font-size: 2rem; font-weight: 800; color: #00d4ff;
            background: rgba(0,212,255,0.08); border: 1px solid rgba(0,212,255,0.3);
            border-radius: 12px; padding: 12px 30px; letter-spacing: 5px;
            text-shadow: 0 0 15px rgba(0,212,255,0.6);
        }
        .captcha-refresh {
            background: none; border: none; color: #00d4ff;
            font-size: 1.4rem; cursor: pointer; transition: 0.3s;
        }
        .captcha-refresh:hover { transform: rotate(180deg); color: #fff; }

        .captcha-input {
            width: 100%; padding: 15px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05); color: #fff;
            font-size: 1.3rem; text-align: center; letter-spacing: 3px;
            outline: none; transition: 0.3s; box-sizing: border-box;
        }
        .captcha-input:focus { border-color: #00d4ff; box-shadow: 0 0 10px rgba(0,212,255,0.2); }

        .captcha-error { color: #ff4466; font-size: 0.8rem; margin: 10px 0; min-height: 20px; font-weight: 600; }

        .captcha-modal-btn {
            width: 100%; padding: 15px; border-radius: 10px; border: none;
            background: linear-gradient(45deg, #00c9ff, #00d4ff);
            color: #000; font-weight: 800; font-size: 1rem;
            cursor: pointer; transition: 0.3s; text-transform: uppercase;
        }
        .captcha-modal-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,212,255,0.4); }
    </style>
</head>
<body>

<?php include 'menu.php'; ?>

<main class="featured-groups" style="padding-top: 157px;">
    <div class="section-title">
        <span class="line"></span>
        <h2>Entre em contacto connosco</h2>
        <span class="line"></span>
    </div>

    <div class="contact-flex-container">
        <section class="card-formulario-split">

            <?php if (isset($enviado) && $enviado): ?>
                <div class="msg-feedback msg-sucesso">&#x2705; Mensagem enviada com sucesso!</div>
            <?php endif; ?>

            <?php if (isset($erro_captcha)): ?>
                <div class="msg-feedback msg-erro"><?php echo htmlspecialchars($erro_captcha); ?></div>
            <?php endif; ?>

            <?php if (isset($erro_envio)): ?>
                <div class="msg-feedback msg-erro"><?php echo htmlspecialchars($erro_envio); ?></div>
            <?php endif; ?>
            
            <?php if (!isset($enviado)): ?>
            <form id="mainForm">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" id="f_nome" placeholder="Teu nome aqui..." required>
                </div>
                <div class="form-group">
                    <label>O Teu Email</label>
                    <input type="email" id="f_email" placeholder="exemplo@email.com" required>
                </div>
                <div class="form-group">
                    <label>Mensagem</label>
                    <textarea id="f_mensagem" rows="4" placeholder="Como podemos ajudar?" required></textarea>
                </div>
                <button type="button" class="btn-enviar-neon" onclick="abrirCaptcha()">Enviar Formulário</button>
            </form>

            <form id="realForm" action="Contactos.php" method="POST" style="display:none;">
                <input type="hidden" name="nome"     id="h_nome">
                <input type="hidden" name="email"    id="h_email">
                <input type="hidden" name="mensagem" id="h_mensagem">
                <input type="hidden" name="captcha"  id="h_captcha">
            </form>
            <?php endif; ?>

        </section>

        <aside class="right-column">
            <div class="info-text-card">
                <h3 class="cyan-text">COORDENADAS</h3>
                <p><i class="fas fa-map-marker-alt"></i> Albergaria-a-Velha, Aveiro</p>
                <p><i class="fas fa-envelope"></i> kpopuniverse.pap@gmail.com</p>
                <br>
                <h3 class="cyan-text">SUGESTÕES</h3>
                <p><i class="fas fa-lightbulb"></i> Envia-nos as tuas sugestões ou reporta bugs para melhorarmos o nosso website!</p>
            </div>
        </aside>
    </div>
</main>

<div class="captcha-modal-overlay" id="captchaModal">
    <div class="captcha-modal">
        <button class="captcha-modal-close" style="position:absolute; top:15px; right:20px; background:none; border:none; color:#555; font-size:1.5rem; cursor:pointer;" onclick="fecharCaptcha()">&times;</button>
        <h2>Segurança</h2>
        <p>Introduz o código numérico para validar:</p>

        <div class="captcha-math-wrap">
            <span id="captchaMath" class="captcha-math">------</span>
            <button type="button" class="captcha-refresh" onclick="refreshCaptcha()" title="Novo código">&#x21BB;</button>
        </div>

        <input type="number" class="captcha-input" id="captchaInput" placeholder="######">
        <div class="captcha-error" id="captchaError"></div>

        <button class="captcha-modal-btn" onclick="submeterComCaptcha()">Confirmar e Enviar</button>
    </div>
</div>

<script>
function abrirCaptcha() {
    const nome = document.getElementById('f_nome').value.trim();
    const email = document.getElementById('f_email').value.trim();
    const mensagem = document.getElementById('f_mensagem').value.trim();

    if (!nome || !email || !mensagem) {
        alert("Por favor, preenche todos os campos.");
        return;
    }

    // Copiar dados para o formulário oculto
    document.getElementById('h_nome').value = nome;
    document.getElementById('h_email').value = email;
    document.getElementById('h_mensagem').value = mensagem;

    // Gerar código e abrir modal
    refreshCaptcha();
    document.getElementById('captchaModal').classList.add('active');
}

function fecharCaptcha() {
    document.getElementById('captchaModal').classList.remove('active');
}

function refreshCaptcha() {
    // Busca o número gerado pelo captcha.php
    fetch('captcha.php?t=' + Date.now())
        .then(response => response.json())
        .then(data => {
            document.getElementById('captchaMath').textContent = data.codigo;
        });
    document.getElementById('captchaInput').value = '';
    document.getElementById('captchaError').textContent = '';
}

function submeterComCaptcha() {
    const inputVal = document.getElementById('captchaInput').value.trim();
    const realCode = document.getElementById('captchaMath').textContent;

    if (inputVal === "") {
        document.getElementById('captchaError').textContent = "Introduz o código.";
        return;
    }

    if (inputVal !== realCode) {
        document.getElementById('captchaError').textContent = "Código incorreto. Tenta novamente.";
        refreshCaptcha();
        return;
    }

    // Correto — envia o código para o servidor validar contra a sessão
    document.getElementById('h_captcha').value = inputVal;
    document.getElementById('realForm').submit();
}

// Permitir fechar ao clicar fora
window.onclick = function(event) {
    let modal = document.getElementById('captchaModal');
    if (event.target == modal) fecharCaptcha();
}
</script>
</body>
</html>