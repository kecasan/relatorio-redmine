<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Documento;
use App\ConversorPDF;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$tipoRelatorio = filter_input(INPUT_POST, 'tipo_relatorio', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'planejamento';
$demandas = $_POST['demandas'] ?? [];

// Organiza dados gerais (campos simples)
$dadosGerais = [
    'contrato' => filter_input(INPUT_POST, 'contrato', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'numero_os' => filter_input(INPUT_POST, 'numero_os', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'nome_projeto' => filter_input(INPUT_POST, 'nome_projeto', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'processo_sei' => filter_input(INPUT_POST, 'processo_sei', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'sape' => filter_input(INPUT_POST, 'sape', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'product_owner' => filter_input(INPUT_POST, 'product_owner', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'lider_projeto' => filter_input(INPUT_POST, 'lider_projeto', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'numero_sprint' => filter_input(INPUT_POST, 'numero_sprint', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    'periodo_sprint' => filter_input(INPUT_POST, 'periodo_sprint', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
];

try {
    $dirTemp = __DIR__ . '/../storage/temporarios/';
    if (!is_dir($dirTemp)) {
        mkdir($dirTemp, 0777, true);
    }

    $idUnico = time();
    $nomeDocx = 'preview_' . $idUnico . '.docx';
    $caminhoDocx = $dirTemp . $nomeDocx;

    // 1. Gera o DOCX
    $documento = new Documento($tipoRelatorio);
    $documento->gerarDocx($dadosGerais, $demandas, $caminhoDocx);

    // 2. Converte para PDF usando o LibreOffice
    $conversor = new ConversorPDF();
    $caminhoPdf = $conversor->converterParaPdf($caminhoDocx, $dirTemp);

    $nomePdf = basename($caminhoPdf);

} catch (\Exception $e) {
    die("<h3>Erro ao processar prévia:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p><p><a href='index.php'>Voltar</a></p>");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévia do Relatório - Sprint</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1000px; margin: 20px auto;">
        <h1>Prévia do Relatório Gerado</h1>

        <div class="actions" style="margin-bottom: 20px;">
            <a href="index.php" class="button" style="background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px">&larr; Voltar e Editar</a>

            <a href="confirmar.php?arquivo=<?= urlencode($nomePdf) ?>" class="button" style="background-color: #28a745; color: white; padding: 10px 15 px; text-decoration: none; border-radius: 4px; margin-left: 10px;">Confirmar e Finalizar</a>
        </div>

        <div class="pdf-viwer" style="border: 1px solid #ccc; height: 600px;">
            <!-- Exibe o PDF diretamente na tela -->
             <iframe src="ver-pdf.php?arquivo=<?= urlencode($nomePdf) ?>" width="100%" height="100%" style="border: none;">
                Seu navegador não suporta a exibição de PDFs integrados. <a href="ver-pdf.php?arquivo=<?= urlencode($nomePdf) ?>" target="_blank">Clique aqui para baizar o PDF</a>
             </iframe>
        </div>
    </div>
</body>
</html>