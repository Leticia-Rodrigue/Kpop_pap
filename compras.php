<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'bd_connection.php';

if (!isset($_SESSION['user_id'])) { header("Location: registar.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 1. CONSULTA COM A COLUNA REFERENCIA
$query = "SELECT p.nome, p.imagem_url, v.data_venda, p.preco, v.referencia, v.quantidade 
          FROM vendas v 
          JOIN produtos p ON v.id_produto = p.id_produto 
          WHERE v.id_utilizador = $user_id 
          ORDER BY v.data_venda DESC";

$compras = mysqli_query($conn, $query);

// VERIFICAÇÃO DE SEGURANÇA: Se a query der erro (ex: falta a coluna na BD)
if (!$compras) {
    die("<div style='color:white; background:red; padding:20px;'>
            Erro na Base de Dados: " . mysqli_error($conn) . "<br><br>
            <b>Dica:</b> Executa este comando no teu phpMyAdmin (separador SQL): <br>
            <code style='background:#000; padding:5px;'>ALTER TABLE vendas ADD COLUMN referencia VARCHAR(50) AFTER id_produto;</code>
         </div>");
}

// 2. AGRUPAR OS RESULTADOS POR REFERÊNCIA
$encomendas = [];
while($row = mysqli_fetch_assoc($compras)) {
    // Se a referência for nula ou vazia, damos um nome genérico para não dar erro no array
    $ref = !empty($row['referencia']) ? $row['referencia'] : "Sem Referência";
    $encomendas[$ref][] = $row;
}

?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <title>K-Universe | Minhas Compras</title>
    <link rel="stylesheet" href="css/style_agenda.css">
    <style>
        body { background-color: #000; font-family: 'Poppins', sans-serif; color: white; }
        .orders-container { max-width: 850px; margin: 120px auto; padding: 20px; }
        
        .order-group {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid #222;
            border-radius: 15px;
            margin-bottom: 30px;
            overflow: hidden;
            border-left: 4px solid #ff0080; /* Destaque lateral rosa */
        }
        .order-header {
            background: #111;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #222;
        }
        .order-header span { color: #ff0080; font-weight: bold; font-size: 0.85rem; letter-spacing: 1px; }

        .item-row {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #1a1a1a;
        }
        .item-row:last-child { border-bottom: none; }
        .item-row img { width: 60px; height: 60px; border-radius: 8px; object-fit: cover; margin-right: 20px; border: 1px solid #333; }
        .item-info { flex-grow: 1; }
        .item-info h4 { margin: 0; font-size: 1rem; color: #fff; font-weight: 600; }
        .item-info p { margin: 0; font-size: 0.8rem; color: #888; }
        
        .item-price { color: #fff; font-weight: 600; text-align: right; min-width: 80px; }
        
        .order-footer {
            padding: 15px 20px;
            background: rgba(255, 0, 128, 0.08);
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 15px;
            border-top: 1px solid #222;
        }
        .total-label { color: #888; font-size: 0.9rem; font-weight: 400; }
        .total-value { color: #ff0080; font-size: 1.3rem; font-weight: 800; }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    
    <div class="orders-container">
        <h2 style="margin-bottom: 40px; font-weight: 800; text-transform: uppercase; letter-spacing: -1px;">
            <span style="color: #ff0080;"></span> O Meu Histórico de compras
        </h2>
        
        <?php if(!empty($encomendas)): ?>
            <?php foreach($encomendas as $ref => $itens): 
                $total_encomenda = 0;
                $data = date('d/m/Y H:i', strtotime($itens[0]['data_venda']));
            ?>
                <div class="order-group">
                    <div class="order-header">
                        <span>ENCOMENDA: <?php echo $ref; ?></span>
                        <span style="color:#888;"><?php echo $data; ?></span>
                    </div>

                    <?php foreach($itens as $item): 
                        $subtotal = $item['preco'] * $item['quantidade'];
                        $total_encomenda += $subtotal;
                    ?>
                        <div class="item-row">
                            <img src="<?php echo htmlspecialchars($item['imagem_url']); ?>" alt="Produto">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['nome']); ?></h4>
                                <p>Qtd: <?php echo $item['quantidade']; ?> x <?php echo number_format($item['preco'], 2); ?>€</p>
                            </div>
                            <div class="item-price"><?php echo number_format($subtotal, 2); ?>€</div>
                        </div>
                    <?php endforeach; ?>

                    <div class="order-footer">
                        <span class="total-label">Valor Total:</span>
                        <span class="total-value"><?php echo number_format($total_encomenda, 2); ?>€</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align:center; padding: 50px; background: #111; border-radius: 15px; border: 1px dashed #333;">
                <p style="color:#666; margin-bottom: 20px;">Ainda não tens compras efetuadas no K-Universe.</p>
                <a href="produtos.php" style="color:#ff0080; text-decoration:none; font-weight:bold; border: 1px solid #ff0080; padding: 10px 20px; border-radius: 5px;">EXPLORAR LOJA</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>