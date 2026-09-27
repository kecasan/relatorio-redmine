<?php

require_once __DIR__ . '/../vendor/autoload.php';

$arquivo = filter_input(INPUT_GET, 'arquivo', FILTER_SANITIZE_SPECIAL_CHARS);

if (!$arquivo) {
    http_response_code(400);
    die("Ficheiro não encontrado.");
}

// Garante que apenas o nome do ficheiro seja passado (evita Directory Transversal)
$nomeArquivo = basename($arquivo);
$caminhoPdf = __DIR__ ; '/../storage/temporarios/' . $nomeArquivo;

if (!file_exists($caminhoPdf)) {
    http_response_code(404);
    die("Ficheiro PDF temporário não encontrado.");
}

// Define os headers para visualização inline do PDF no navegador
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $nomeArquivo . '"');
header('Content-Length: ' . filesize($caminhoPdf));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Envia o conteudo do PDF para o navegador
readfile($caminhoPdf);
exit;

?>