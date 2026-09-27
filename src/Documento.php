<?php
namespace App;

use PhpOffice\PhpWord\TemplateProcessor;

class Documento
{
    private string $templatePath;

    public function __construct(string $tipoRelatorio)
    {
        $file = ($tipoRelatorio === 'encerramento') ? 'AAAAMMDD - Relatório de Revisão da Sprint XX.docx' : 'AAAAMMDD - Relatório de Planejamento da Sprint XX.docx';
        $this->templatePath = __DIR__ . '/../templates/' . $file;

        if (!file_exists($this->templatePath)) {
            throw new \Exception("Modelo Word não encontrado em: " . $this->templatePath);
        }
    }
    public function gerarDocx(array $dadosGerais, array $demandas, string $caminhoSaida): void
    {
        $template = new TemplateProcessor($this->templatePath);

        // 1. Preenche campos simples do documento
        foreach ($dadosGerais as $chave => $valor) {
            $template->setValue($chave, $valor);
        }

        // 2. Preenche a tabela de demandas usando o cloneRow
        $totalDemandas = count($demandas);

        if ($totalDemandas > 0) {
            // Clona a linha do modelo com base no primeiro marcador da tabela
            $template->cloneRow('id', $totalDemandas);

            foreach ($demandas as $index => $demanda) {
                $i = $index + 1; // 0 cloneRow do PHPWord utiliza índice baseado em 1 (1#, 2#, ...)

                $template->setValue("id#{$i}", $demanda['id'] ?? '');
                $template->setValue("sape_demanda#{$i}", $demanda['sape'] ?? '');
                $template->setValue("descricao#{$i}", $demanda['descricao'] ?? '');
                $template->setValue("pf#{$i}", $demanda['pf'] ?? '');
                $template->setValue("fase#{$i}", $demanda['fase'] ?? '');
                $template->setValue("responsavel#{$i}", $demanda['responsavel'] ?? '');
                $template->setValue("situacao#{$i}", $demanda['situacao'] ?? '');
            }
        }

        // Salva o documento preenchido na pasta de destino
        $template->saveAs($caminhoSaida);
    }
}

?>