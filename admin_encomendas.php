<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'bd_connection.php';

// 1. SEGURANÇA
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || ($_SESSION['role'] != 1 && $_SESSION['role'] != 2)) { 
    die("Acesso negado."); 
}

// 2. QUERY CORRIGIDA
// Removi 'v.preco' (que não existe na tua tabela vendas) 
// e passei a usar 'p.preco' (que vem da tabela produtos)
$query = "SELECT u.nome as cliente, p.nome as produto, p.imagem_url, v.data_venda, p.preco, v.referencia, v.quantidade 
          FROM vendas v 
          JOIN produtos p ON v.id_produto = p.id_produto 
          JOIN utilizadores u ON v.id_utilizador = u.id_utilizador 
          ORDER BY v.data_venda DESC";

$res = mysqli_query($conn, $query);

if (!$res) {
    die("<div style='color:white; background:red; padding:20px;'>
            <h2>Erro na Consulta SQL</h2>
            <p>" . mysqli_error($conn) . "</p>
         </div>");
}

$encomendas_globais = [];
while($row = mysqli_fetch_assoc($res)) {
    $ref = !empty($row['referencia']) ? $row['referencia'] : "Sem Ref";
    $encomendas_globais[$ref][] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin | Gestão de Encomendas</title>
    <link rel="stylesheet" href="css/style_produtos.css"> <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --p-neon: #ff0080; --s-neon: #00ffff; }
        body { background-color: #050505; font-family: 'Poppins', sans-serif; color: white; margin: 0; }
        
        .admin-container { max-width: 1100px; margin: 120px auto 50px; padding: 0 20px; }
        
        .title-section { border-left: 5px solid var(--p-neon); padding-left: 15px; margin-bottom: 40px; }
        .title-section h1 { margin: 0; font-size: 2rem; text-transform: uppercase; letter-spacing: 2px; }

        .order-group { 
            background: rgba(255, 255, 255, 0.03); 
            border: 1px solid #222; 
            border-radius: 15px; 
            margin-bottom: 30px; 
            overflow: hidden;
            transition: 0.3s;
        }
        .order-group:hover { border-color: #444; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }

        .order-header { 
            background: rgba(255, 255, 255, 0.05); 
            padding: 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-bottom: 1px solid #222;
        }

        .cliente-info b { color: var(--s-neon); font-size: 1.1rem; }
        .ref-tag { background: #333; padding: 4px 10px; border-radius: 4px; font-family: monospace; font-size: 0.8rem; color: #ccc; }

        .item-row { 
            display: flex; 
            align-items: center; 
            padding: 15px 25px; 
            border-bottom: 1px solid rgba(255,255,255,0.05); 
        }
        .item-row:last-child { border-bottom: none; }
        
        .item-img { width: 60px; height: 60px; border-radius: 8px; margin-right: 20px; object-fit: cover; border: 1px solid #333; }
        
        .item-details { flex-grow: 1; }
        .item-details h4 { margin: 0; font-weight: 400; color: #eee; }
        .item-details small { color: #888; }

        .item-price { font-weight: 600; color: #fff; }

        .order-footer { 
            background: rgba(0, 0, 0, 0.3); 
            padding: 15px 25px; 
            text-align: right; 
            border-top: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-label { color: #888; text-transform: uppercase; font-size: 0.8rem; }
        .total-value { color: var(--p-neon); font-size: 1.4rem; font-weight: 800; }

        .empty-state { text-align: center; padding: 100px 20px; background: #111; border-radius: 20px; border: 1px dashed #333; }
    </style>
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="admin-container">
        <div class="title-section">
            <h1>Gestão de Encomendas</h1>
            <p style="color: #666;">Histórico global de vendas da loja</p>
        </div>

        <?php if(!empty($encomendas_globais)): ?>
            <?php foreach($encomendas_globais as $ref => $itens): 
                $total_final = 0;
                $cliente_nome = $itens[0]['cliente'];
                $data_formatada = date('d/m/Y | H:i', strtotime($itens[0]['data_venda']));
            ?>
                <div class="order-group">
                    <div class="order-header">
                        <div class="cliente-info">
                            <span>Encomenda de: <b><?php echo htmlspecialchars($cliente_nome); ?></b></span><br>
                            <span class="ref-tag">REF: <?php echo htmlspecialchars($ref); ?></span>
                        </div>
                        <div style="text-align: right;">
                            <span style="display:block; font-size: 0.85rem; color: #888;"><?php echo $data_formatada; ?></span>
                            <span style="color: #44ff44; font-size: 0.75rem;">● Pago</span>
                        </div>
                    </div>

                    <div class="order-body">
                        <?php foreach($itens as $item): 
                            $subtotal = $item['preco'] * $item['quantidade'];
                            $total_final += $subtotal;
                        ?>
                            <div class="item-row">
                                <img src="<?php echo htmlspecialchars($item['imagem_url']); ?>" class="item-img" onerror="this.src='img/placeholder.png'">
                                <div class="item-details">
                                    <h4><?php echo htmlspecialchars($item['produto']); ?></h4>
                                    <small>Preço Unitário: <?php echo number_format($item['preco'], 2); ?>€ | Qtd: <?php echo $item['quantidade']; ?></small>
                                </div>
                                <div class="item-price">
                                    <?php echo number_format($subtotal, 2); ?>€
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-footer">
                        <span class="total-label">Valor total da venda</span>
                        <span class="total-value"><?php echo number_format($total_final, 2); ?>€</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2 style="color: #333;">Nenhuma venda encontrada</h2>
                <p>Assim que um cliente finalizar uma compra, ela aparecerá aqui.</p>
                <a href="produtos.php" style="color: var(--s-neon); text-decoration: none;">Ir para a Loja →</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>