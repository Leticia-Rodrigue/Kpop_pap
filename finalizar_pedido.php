<?php
require 'bd_connection.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) { header("Location: registar.php"); exit(); }

$user_id = intval($_SESSION['user_id']);
$passo   = 1;
$erro    = '';
$referencia = '';
$metodo  = '';
$total   = 0;
$carrinho = [];

// Guardar carrinho quando vem de produtos.php
if (isset($_POST['carrinho_data'])) {
    $_SESSION['carrinho_checkout'] = $_POST['carrinho_data'];
}

if (empty($_SESSION['carrinho_checkout'])) {
    header("Location: produtos.php"); exit();
}

$carrinho = json_decode($_SESSION['carrinho_checkout'], true) ?: [];
$total    = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $carrinho));

// Passo 2 — ir para pagamento
if (isset($_POST['ir_pagamento'])) {
    $passo = 2;
}

// Passo 3 — confirmar
elseif (isset($_POST['confirmar_pagamento'])) {
    $metodo = $_POST['metodo'] ?? 'multibanco';

    // Validar cartão
    if ($metodo === 'cartao') {
        $num = preg_replace('/[^0-9]/', '', $_POST['num_cartao'] ?? '');
        $cvv = trim($_POST['cvv'] ?? '');
        $val = trim($_POST['validade'] ?? '');
        if (strlen($num) < 16 || strlen($cvv) < 3 || empty($val)) {
            $erro  = 'Preenche todos os dados do cartão corretamente.';
            $passo = 2;
        }
    }

    if ($metodo === 'multibanco') {
        $mb_ent = trim($_POST['mb_entidade'] ?? '');
        $mb_ref = trim($_POST['mb_referencia'] ?? '');
        if (empty($mb_ent) || empty($mb_ref)) {
            $erro  = 'Preenche a Entidade e a Referência do Multibanco.';
            $passo = 2;
        }
    }

    if (!$erro) {
        $ref_unica = uniqid('ORD_');
        $ref_safe  = mysqli_real_escape_string($conn, $ref_unica);
        $ok        = true;

        foreach ($carrinho as $item) {
            $id_prod = intval($item['id']);
            $qty     = intval($item['qty']);
            $sql = "INSERT INTO vendas (id_utilizador, id_produto, referencia, data_venda, quantidade)
                    VALUES ($user_id, $id_prod, '$ref_safe', NOW(), $qty)";
            if (!mysqli_query($conn, $sql)) {
                $ok   = false;
                $erro = 'Erro SQL: ' . mysqli_error($conn);
            }
        }

        if ($ok) {
            $passo      = 3;
            $referencia = strtoupper(substr(md5($ref_unica),0,3)) . ' '
                        . strtoupper(substr(md5($ref_unica),3,3)) . ' '
                        . strtoupper(substr(md5($ref_unica),6,3));
            unset($_SESSION['carrinho_checkout']);
        } else {
            $passo = 2;
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
    <title>K-Pop Universe | Checkout</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style_agenda.css">
    <style>
        :root { --pink:#ff0080; --cyan:#00d4ff; --dark:#050505; --card:#0d0d0d; }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--dark); color: #fff; font-family: "Poppins", sans-serif; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        .page { max-width: 780px; margin: 0 auto; padding: 80px 24px 60px; }
        .spacer { height: 60px; }

        /* STEPS */
        .steps { display: flex; align-items: center; justify-content: center; margin-bottom: 50px; }
        .step { display: flex; flex-direction: column; align-items: center; gap: 6px; }
        .step-circle { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; font-weight: 800; border: 2px solid #2a2a2a; color: #333; background: #0d0d0d; transition: all 0.3s; }
        .step.active .step-circle { border-color: var(--cyan); color: var(--cyan); box-shadow: 0 0 14px rgba(0,212,255,0.3); }
        .step.done .step-circle   { border-color: var(--pink); background: var(--pink); color: #000; }
        .step-label { font-size: 0.6rem; letter-spacing: 1px; text-transform: uppercase; color: #333; }
        .step.active .step-label { color: var(--cyan); }
        .step.done .step-label   { color: var(--pink); }
        .step-line { width: 70px; height: 1px; background: #2a2a2a; margin-bottom: 22px; transition: background 0.3s; }
        .step-line.done { background: var(--pink); }

        .box { background: var(--card); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; padding: 28px; margin-bottom: 20px; }
        .box-title { font-size: 0.68rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: #555; margin-bottom: 20px; }

        .item-row { display: flex; align-items: center; gap: 14px; padding: 11px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .item-row:last-child { border-bottom: none; }
        .item-img { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; background: #111; flex-shrink: 0; }
        .item-name { flex: 1; font-size: 0.85rem; font-weight: 600; }
        .item-qty { font-size: 0.72rem; color: #444; margin-top: 2px; }
        .item-price { font-size: 0.92rem; font-weight: 800; color: var(--cyan); }
        .total-row { display: flex; justify-content: space-between; align-items: center; padding-top: 18px; margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.07); }
        .total-label { font-size: 0.7rem; color: #555; text-transform: uppercase; letter-spacing: 2px; }
        .total-val { font-size: 1.7rem; font-weight: 900; color: var(--pink); text-shadow: 0 0 20px rgba(255,0,128,0.3); }

        .metodos { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; margin-bottom: 24px; }
        .metodo-input { display: none; }
        .metodo-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 18px 14px; text-align: center; cursor: pointer; transition: all 0.3s; }
        .metodo-card:hover { border-color: rgba(0,212,255,0.3); }
        .metodo-input:checked + .metodo-card { border-color: var(--cyan); background: rgba(0,212,255,0.06); box-shadow: 0 0 20px rgba(0,212,255,0.1); }
        .metodo-icon { font-size: 1.8rem; margin-bottom: 8px; }
        .metodo-nome { font-size: 0.72rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #aaa; }

        .mb-box { background: rgba(0,212,255,0.04); border: 1px solid rgba(0,212,255,0.15); border-radius: 14px; padding: 20px; }
        .mb-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .mb-row:last-child { border-bottom: none; }
        .mb-key { font-size: 0.7rem; color: #555; text-transform: uppercase; letter-spacing: 1px; }
        .mb-val { font-size: 0.95rem; font-weight: 800; color: var(--cyan); letter-spacing: 2px; }
        .mbway-box { background: rgba(255,0,128,0.04); border: 1px solid rgba(255,0,128,0.15); border-radius: 14px; padding: 20px; }

        .cartao-box { display: flex; flex-direction: column; gap: 14px; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .field-group label { display: block; font-size: 0.65rem; color: #555; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
        .field-group input { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04); color: #fff; font-family: "Poppins", sans-serif; font-size: 0.88rem; outline: none; transition: border-color 0.3s; }
        .field-group input:focus { border-color: rgba(0,212,255,0.4); }
        .field-group input::placeholder { color: #333; }

        .btn-main { width: 100%; padding: 14px; border-radius: 12px; border: none; background: linear-gradient(45deg, var(--pink), var(--cyan)); color: #000; font-weight: 800; font-size: 0.9rem; letter-spacing: 2px; text-transform: uppercase; cursor: pointer; transition: all 0.3s; margin-top: 8px; }
        .btn-main:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(255,0,128,0.3); }
        .erro-box { background: rgba(255,68,102,0.1); border: 1px solid rgba(255,68,102,0.3); border-radius: 12px; padding: 14px 18px; color: #ff4466; font-size: 0.85rem; margin-bottom: 18px; }
        .nota-simulado { font-size: 0.7rem; color: #333; margin-top: 10px; text-align: center; font-style: italic; }

        .success-wrap { text-align: center; padding: 20px 0; }
        .success-icon { font-size: 4rem; margin-bottom: 20px; animation: popIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275); }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .success-wrap h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 10px; }
        .success-wrap p { color: #555; font-size: 0.88rem; line-height: 1.7; max-width: 400px; margin: 0 auto 20px; }
        .ref-badge { display: inline-block; background: rgba(0,212,255,0.08); border: 1px solid rgba(0,212,255,0.2); border-radius: 12px; padding: 14px 28px; font-size: 1.1rem; font-weight: 800; color: var(--cyan); letter-spacing: 4px; margin-bottom: 24px; }
        .btn-voltar-loja { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; border-radius: 50px; border: 1px solid var(--pink); color: var(--pink); font-weight: 700; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; transition: all 0.3s; }
        .btn-voltar-loja:hover { background: var(--pink); color: #000; }

        @media (max-width: 600px) { .metodos { grid-template-columns: 1fr; } .form-row-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="page">
    <div class="spacer"></div>
    <!-- STEPS -->
    <div class="steps">
        <div class="step <?php echo $passo == 1 ? 'active' : 'done'; ?>">
            <div class="step-circle"><?php echo $passo > 1 ? '✓' : '1'; ?></div>
            <div class="step-label">Resumo</div>
        </div>
        <div class="step-line <?php echo $passo > 1 ? 'done' : ''; ?>"></div>
        <div class="step <?php echo $passo == 2 ? 'active' : ($passo > 2 ? 'done' : ''); ?>">
            <div class="step-circle"><?php echo $passo > 2 ? '✓' : '2'; ?></div>
            <div class="step-label">Pagamento</div>
        </div>
        <div class="step-line <?php echo $passo > 2 ? 'done' : ''; ?>"></div>
        <div class="step <?php echo $passo == 3 ? 'active' : ''; ?>">
            <div class="step-circle">3</div>
            <div class="step-label">Confirmação</div>
        </div>
    </div>

<?php if ($passo === 1): ?>
    <!-- PASSO 1 -->

    <form method="POST">
        <input type="hidden" name="carrinho_data" value="<?php echo htmlspecialchars($_SESSION['carrinho_checkout'] ?? ''); ?>">
        <div class="box">
            <div class="box-title">Resumo da Encomenda</div>
            <?php foreach ($carrinho as $item): ?>
            <div class="item-row">
                <img class="item-img" src="<?php echo htmlspecialchars($item['img']); ?>" onerror="this.src='img/kpop_logo.png'">
                <div style="flex:1">
                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="item-qty"><?php echo $item['qty']; ?>x · <?php echo number_format($item['price'],2); ?>€ cada</div>
                </div>
                <div class="item-price"><?php echo number_format($item['price'] * $item['qty'],2); ?>€</div>
            </div>
            <?php endforeach; ?>
            <div class="total-row">
                <span class="total-label">Total a pagar</span>
                <span class="total-val"><?php echo number_format($total,2); ?>€</span>
            </div>
        </div>
        <button type="submit" name="ir_pagamento" class="btn-main">
            <i class="fas fa-lock"></i> &nbsp; Escolher Método de Pagamento
        </button>
    </form>

<?php elseif ($passo === 2): ?>
    <!-- PASSO 2 -->
    <form method="POST" id="formPagamento">
        <input type="hidden" name="carrinho_data" value="<?php echo htmlspecialchars($_SESSION['carrinho_checkout'] ?? ''); ?>">
        <?php if ($erro): ?><div class="erro-box"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erro); ?></div><?php endif; ?>

        <div class="box">
            <div class="box-title">Método de Pagamento</div>
            <div class="metodos">
                <label class="metodo-label">
                    <input class="metodo-input" type="radio" name="metodo" value="multibanco" checked onchange="mostrarMetodo('multibanco')">
                    <div class="metodo-card"><div class="metodo-icon">🏧</div><div class="metodo-nome">Multibanco</div></div>
                </label>
                <label class="metodo-label">
                    <input class="metodo-input" type="radio" name="metodo" value="mbway" onchange="mostrarMetodo('mbway')">
                    <div class="metodo-card"><div class="metodo-icon">📱</div><div class="metodo-nome">MB WAY</div></div>
                </label>
                <label class="metodo-label">
                    <input class="metodo-input" type="radio" name="metodo" value="cartao" onchange="mostrarMetodo('cartao')">
                    <div class="metodo-card"><div class="metodo-icon">💳</div><div class="metodo-nome">Cartão</div></div>
                </label>
            </div>

            <div id="dados-multibanco">
                <div class="cartao-box">
                    <div class="field-group">
                        <label>Entidade</label>
                        <input type="text" name="mb_entidade" placeholder="ex: 21205" maxlength="10">
                    </div>
                    <div class="field-group">
                        <label>Referência</label>
                        <input type="text" name="mb_referencia" placeholder="ex: 123 456 789" maxlength="15">
                    </div>
                    <div class="mb-box" style="margin-top:4px;">
                        <div class="mb-row"><span class="mb-key">Montante</span><span class="mb-val"><?php echo number_format($total,2); ?>€</span></div>
                        <div class="mb-row"><span class="mb-key">Validade</span><span class="mb-val"><?php echo date('d/m/Y', strtotime('+3 days')); ?></span></div>
                    </div>
                </div>
                <p class="nota-simulado">⚠️ Pagamento simulado — sem transação real</p>
            </div>
            <div id="dados-mbway" style="display:none">
                <div class="mbway-box">
                    <div class="field-group">
                        <label>Número de Telemóvel</label>
                        <input type="tel" name="telemovel" placeholder="9XX XXX XXX" maxlength="9">
                    </div>
                    <p style="font-size:0.75rem;color:#555;margin-top:12px;text-align:center;">Receberás uma notificação para aprovar <strong style="color:var(--pink)"><?php echo number_format($total,2); ?>€</strong></p>
                    <p class="nota-simulado">⚠️ Pagamento simulado — sem transação real</p>
                </div>
            </div>
            <div id="dados-cartao" style="display:none">
                <div class="cartao-box">
                    <div class="field-group">
                        <label>Número do Cartão</label>
                        <input type="text" name="num_cartao" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCartao(this)">
                    </div>
                    <div class="form-row-2">
                        <div class="field-group"><label>Validade</label><input type="text" name="validade" placeholder="MM/AA" maxlength="5" oninput="formatValidade(this)"></div>
                        <div class="field-group"><label>CVV</label><input type="password" name="cvv" placeholder="•••" maxlength="4"></div>
                    </div>
                    <div class="field-group"><label>Nome no Cartão</label><input type="text" name="nome_cartao" placeholder="NOME APELIDO"></div>
                    <p class="nota-simulado">⚠️ Dados não processados — projeto académico</p>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="total-row" style="padding-top:0;margin-top:0;border-top:none;">
                <span class="total-label"><?php echo count($carrinho); ?> artigo(s)</span>
                <span class="total-val"><?php echo number_format($total,2); ?>€</span>
            </div>
        </div>
        <button type="submit" name="confirmar_pagamento" class="btn-main" id="btnPagar">
            <i class="fas fa-check-circle"></i> &nbsp; Confirmar Pagamento
        </button>
    </form>

<?php elseif ($passo === 3): ?>
    <!-- PASSO 3 -->

    <div class="box">
        <div class="success-wrap">
            <div class="success-icon">✅</div>
            <h2>Encomenda Confirmada!</h2>
            <p>A tua compra foi registada com sucesso. Guarda a referência abaixo para acompanhar a tua encomenda.</p>
            <div class="ref-badge"><?php echo $referencia; ?></div><br><br>
            <?php $metodo_label = match($metodo) { 'mbway' => '📱 MB WAY', 'cartao' => '💳 Cartão', default => '🏧 Multibanco' }; ?>
            <p style="color:#444;font-size:0.8rem;margin-bottom:24px;">
                Método: <strong style="color:#fff"><?php echo $metodo_label; ?></strong> &nbsp;·&nbsp;
                Total: <strong style="color:var(--pink)"><?php echo number_format($total,2); ?>€</strong>
            </p>
            <p class="nota-simulado">⚠️ Projeto académico — nenhum pagamento real foi efetuado</p>
            <br><br>
            <a href="produtos.php" class="btn-voltar-loja"><i class="fas fa-arrow-left"></i> Voltar à Loja</a>
        </div>
    </div>
<?php endif; ?>
</div>

<script>
function mostrarMetodo(val) {
    document.getElementById('dados-multibanco').style.display = val === 'multibanco' ? 'block' : 'none';
    document.getElementById('dados-mbway').style.display      = val === 'mbway'      ? 'block' : 'none';
    document.getElementById('dados-cartao').style.display     = val === 'cartao'     ? 'block' : 'none';
}
function formatCartao(input) {
    let v = input.value.replace(/[^0-9]/g,'').substring(0,16);
    input.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function formatValidade(input) {
    let v = input.value.replace(/[^0-9]/g,'');
    if (v.length >= 2) v = v.substring(0,2) + '/' + v.substring(2,4);
    input.value = v;
}
document.getElementById('formPagamento')?.addEventListener('submit', function() {
    const btn = document.getElementById('btnPagar');
    const hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = 'confirmar_pagamento'; hidden.value = '1';
    this.appendChild(hidden);
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> &nbsp; A processar...';
    btn.disabled = true;
});
</script>
</body>
</html>