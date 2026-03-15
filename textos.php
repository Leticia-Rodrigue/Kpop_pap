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

function saveTextos(PDO $pdo, array $conteudos): bool
{
    if (empty($conteudos)) {
        return true;
    }

    $sqlSelect = "SELECT COUNT(*) FROM conteudos_paginas WHERE chave_slug = ?";
    $sqlUpdate = "UPDATE conteudos_paginas SET texto_html = ? WHERE chave_slug = ?";
    $sqlInsert = "INSERT INTO conteudos_paginas (chave_slug, texto_html) VALUES (?, ?)";

    try {
        $pdo->beginTransaction();

        $stmtSelect = $pdo->prepare($sqlSelect);
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtInsert = $pdo->prepare($sqlInsert);

        foreach ($conteudos as $slug => $textoHtml) {
            $slug = (string) $slug;
            $textoHtml = trim((string) $textoHtml);

            $stmtSelect->execute([$slug]);
            $exists = (int) $stmtSelect->fetchColumn() > 0;

            if ($exists) {
                $stmtUpdate->execute([$textoHtml, $slug]);
            } else {
                $stmtInsert->execute([$slug, $textoHtml]);
            }
        }

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log('saveTextos error: ' . $e->getMessage());
        return false;
    }
}

?>