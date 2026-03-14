<?php
function getTextos(PDO $pdo, array $slugs){
    if (empty($slugs)) {
        return [];
    }
    $slugholder = str_repeat('?,', count($slugs) - 1) . '?';

    // Seleciona o texto armazenado na coluna `texto_html` juntamente com o slug
    $sql = "SELECT chave_slug, texto_html FROM conteudos_paginas WHERE chave_slug IN ($slugholder)";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($slugs);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('getTextos error: ' . $e->getMessage());
        return [];
    }

    $textos = [];
    foreach ($results as $row) {
        $textos[$row['chave_slug']] = $row['texto_html'];
    }

    return $textos;

}

?>