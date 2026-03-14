<?php
session_start();
include 'bd_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['carrinho_data'])) {
    header("Location: produtos.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$carrinho = json_decode($_POST['carrinho_data'], true);
$referencia_unica = uniqid('ORD_'); // Gera um ID único para este grupo de compras

if (!empty($carrinho)) {
    foreach ($carrinho as $item) {
        $id_prod = (int)$item['id'];
        $qty = (int)$item['qty'];
        
        // Adicionamos a $referencia_unica na query
        $sql = "INSERT INTO vendas (id_utilizador, id_produto, quantidade, data_venda, referencia) 
                VALUES ($user_id, $id_prod, $qty, NOW(), '$referencia_unica')";
        mysqli_query($conn, $sql);
    }
    header("Location: compras.php?sucesso=1");
} else {
    header("Location: produtos.php");
}
?>