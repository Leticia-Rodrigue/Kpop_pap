<?php 
require 'textos.php';
require 'bd_connection.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// 1. Procurar produtos na Base de Dados
$query = "SELECT * FROM produtos ORDER BY id_produto DESC";
$res_produtos = mysqli_query($conn, $query);

if (!$res_produtos) {
    die("Erro na consulta SQL: " . mysqli_error($conn));
}

$total_db = mysqli_num_rows($res_produtos);
$user_logado = isset($_SESSION['user_id']) ? 'true' : 'false';
?>
<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="img/kpop_logo.png">
    <title>K-Pop Universe | Loja</title>
    <link rel="stylesheet" href="css/style_produtos.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'menu.php'; ?>
<br><br><br><br>

<div class="container-loja">
    <aside class="sidebar-filtros">
        <div class="card-sidebar">
            <h3>FILTROS</h3>
            <div class="filter-section">
                <h4>CATEGORIA</h4>
                <label class="neon-check">Álbuns <input type="checkbox" class="filter-input" value="Álbuns"><span class="checkmark"></span></label>
                <label class="neon-check">Lightsticks <input type="checkbox" class="filter-input" value="Lightsticks"><span class="checkmark"></span></label>
                <label class="neon-check">Vestuário <input type="checkbox" class="filter-input" value="Vestuário"><span class="checkmark"></span></label>
                <label class="neon-check">Porta-chaves <input type="checkbox" class="filter-input" value="Porta-chaves"><span class="checkmark"></span></label>
                <label class="neon-check">Peluches <input type="checkbox" class="filter-input" value="Peluches"><span class="checkmark"></span></label>
            </div>
            
            <div class="filter-section">
                <h4>GRUPOS</h4>
                <label class="neon-check">BTS <input type="checkbox" class="filter-input" value="BTS"><span class="checkmark"></span></label>
                <label class="neon-check">BLACKPINK <input type="checkbox" class="filter-input" value="Blackpink"><span class="checkmark"></span></label>
                <label class="neon-check">Stray Kids <input type="checkbox" class="filter-input" value="Stray Kids"><span class="checkmark"></span></label>
                <label class="neon-check">IVE <input type="checkbox" class="filter-input" value="IVE"><span class="checkmark"></span></label>
                <label class="neon-check">RIIZE <input type="checkbox" class="filter-input" value="RIIZE"><span class="checkmark"></span></label>
            </div>
            
            <button onclick="clearFilters()" style="background:none; border:1px solid #ff0080; color:#ff0080; padding:8px; border-radius:5px; cursor:pointer; width:100%; margin-top:15px; font-weight:bold;">LIMPAR FILTROS</button>
        </div>
    </aside>

    <main class="content-loja">
        <div class="toolbar-loja">
            <span>A mostrar <b id="visible-count" style="color:#ff0080"><?php echo $total_db; ?></b> produtos</span>
            <div class="sort-box">
                <label>ORDENAR POR:</label>
                <select id="sort-select" onchange="sortProducts()">
                    <option value="default">Mais Recentes</option>
                    <option value="low-high">Preço: Baixo-Alto</option>
                    <option value="high-low">Preço: Alto-Baixo</option>
                </select>
            </div>
        </div>

        <div class="grid-produtos" id="grid-produtos">
            <?php while($p = mysqli_fetch_assoc($res_produtos)): ?>
            <div class="product-card" 
                 data-categoria="<?php echo htmlspecialchars($p['categoria']); ?>" 
                 data-grupo="<?php echo htmlspecialchars($p['grupo'] ?? ''); ?>" 
                 data-price="<?php echo $p['preco']; ?>">
                
                <div class="img-wrap" style="background-image: url('<?php echo htmlspecialchars($p['imagem_url']); ?>')">
                    <?php if($p['id_produto'] > ($total_db - 2)) echo '<span class="tag-novo">NOVO</span>'; ?>
                </div>

                <div class="info-wrap">
                    <span class="brand"><?php echo htmlspecialchars($p['categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                    <div class="price"><?php echo number_format($p['preco'], 2); ?>€</div>
                    <div class="footer-card">
                        <span class="label-tech"><?php echo htmlspecialchars($p['descricao']); ?></span>
                        <button class="add-btn-neon" onclick="addToCart(<?php echo $p['id_produto']; ?>, '<?php echo addslashes($p['nome']); ?>', <?php echo $p['preco']; ?>, '<?php echo $p['imagem_url']; ?>')">+</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
</div>

<div id="cartOverlay" class="cart-overlay" onclick="toggleCart()"></div>
<div id="cartDrawer" class="cart-drawer">
    <div class="cart-header">
        <h2>O TEU CARRINHO</h2>
        <span class="close-btn" onclick="toggleCart()">&times;</span>
    </div>
    <div class="cart-body">
        <table class="cart-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>PREÇO</th>
                    <th>QNT</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="cartItems"></tbody>
        </table>
    </div>
    <div class="cart-footer">
        <div class="total-box">
            <span>Total</span>
            <span id="cartTotal">0.00€</span>
        </div>
        <button class="btn-checkout-neon" onclick="finalizarCompra()">FINALIZAR COMPRA</button>
    </div>
</div>

<script>
const utilizadorLogado = <?php echo $user_logado; ?>;
let cart = [];

// --- FUNÇÕES DO CARRINHO ---
function toggleCart() {
    document.getElementById('cartDrawer').classList.toggle('active');
    document.getElementById('cartOverlay').classList.toggle('active');
}

function addToCart(id, name, price, img) {
    const itemExistente = cart.find(item => item.id === id);
    if (itemExistente) {
        itemExistente.qty++;
    } else {
        cart.push({ id, name, price, img, qty: 1 });
    }
    renderCart();
    if(!document.getElementById('cartDrawer').classList.contains('active')) toggleCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const totalEl = document.getElementById('cartTotal');
    container.innerHTML = '';
    let total = 0;

    cart.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        container.innerHTML += `
            <tr>
                <td>
                    <div class="item-cell">
                        <img src="${item.img}" width="30" style="border-radius: 5px;">
                        <span>${item.name}</span>
                    </div>
                </td>
                <td>${item.price.toFixed(2)}€</td>
                <td style="text-align: center;"><strong>${item.qty}x</strong></td>
                <td>
                    <button onclick="removeItem(${index})" style="background:none; border:none; color:#ff0080; cursor:pointer; font-weight:bold;">&times;</button>
                </td>
            </tr>`;
    });
    totalEl.innerText = total.toFixed(2) + '€';
}

function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function finalizarCompra() {
    if (cart.length === 0) { alert("O teu carrinho está vazio!"); return; }
    if (utilizadorLogado) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'finalizar_pedido.php';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'carrinho_data';
        input.value = JSON.stringify(cart);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    } else {
        alert("Faz login para comprar!");
        window.location.href = "registar.php";
    }
}

// --- LÓGICA DE FILTROS E ORDENAÇÃO ---
const filterInputs = document.querySelectorAll('.filter-input');

filterInputs.forEach(input => {
    input.addEventListener('change', filterProducts);
});

function filterProducts() {
    // Usar querySelectorAll de novo para apanhar os cards mesmo após reordenação
    const productCards = document.querySelectorAll('.product-card');
    const activeFilters = Array.from(filterInputs)
        .filter(i => i.checked)
        .map(i => i.value.trim()); // .trim() por segurança

    let count = 0;
    productCards.forEach(card => {
        const categoria = card.getAttribute('data-categoria').trim();
        const grupo = card.getAttribute('data-grupo').trim();

        if (activeFilters.length === 0 || activeFilters.includes(categoria) || activeFilters.includes(grupo)) {
            card.style.display = 'block';
            count++;
        } else {
            card.style.display = 'none';
        }
    });
    document.getElementById('visible-count').innerText = count;
}

function clearFilters() {
    filterInputs.forEach(i => i.checked = false);
    filterProducts();
}

function sortProducts() {
    const grid = document.getElementById('grid-produtos');
    const select = document.getElementById('sort-select').value;
    const cardsArray = Array.from(document.querySelectorAll('.product-card'));

    cardsArray.sort((a, b) => {
        const priceA = parseFloat(a.getAttribute('data-price'));
        const priceB = parseFloat(b.getAttribute('data-price'));

        if (select === 'low-high') return priceA - priceB;
        if (select === 'high-low') return priceB - priceA;
        return 0;
    });

    grid.innerHTML = '';
    cardsArray.forEach(card => grid.appendChild(card));
    // Re-aplicar filtros activos após reordenar
    filterProducts();
}
</script>
</body>
</html>