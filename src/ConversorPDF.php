<?php

namespace App;

class ConversorPDF
{
    private string $caminhoLibreOffice;

    public function __construct()
    {
        // Caminho padrão do LibreOffice no Windows 11
        $this->caminhoLibreOffice = '"C:\\Program Files\\LibreOffice\\program\\soffice.exe"';
    }

    /**
     * Converte um arquivo DOCX para PDF utilizando o LibreOffice Headless 
    */
    public function converterParaPdf(string $caminhoDocx, string $diretorioSaida): string
    {
        if (!file_exists($caminhoDocx)) {
            throw new \Exception("Arquivo DOCX não encontrado para conversão: " . $caminhoDocx);
        }

        // Garante que o diretório de saída existe
        if (!is_dir($diretorioSaida)) {
            mkdir($diretorioSaida, 0777, true);
        }

        // Monte o comando de conversão para o Windows
        // --headless: roda sem abrir interface gráfica
        // --convert-to pdf: formato final
        // --outdir: diretório onde o PDF será salvo
        $comando = "{$this->caminhoLibreOffice} --headless --convert-to pdf " . escapeshellarg($caminhoDocx) . " --outdir " . escapeshellarg($diretorioSaida);

        $resultado = null;
        $codigoRetorno = null;

        // Executa o comando de sistema
        exec($comando . " 2>&1", $resultado, $codigoRetorno);

        // Gera o nome do arquivo PDF esperado
        $nomeDocx = pathinfo($caminhoDocx, PATHINFO_FILENAME);
        $caminhoPdfEsperado = rtrim($diretorioSaida, '/\\') . DIRECTORY_SEPARATOR . $nomeDocx . '.pdf';

        if (!file_exists($caminhoPdfEsperado)) {
            $erroDetalhado = implode("\n", $resultado);
            throw new \Exception("Falha na conversão para PDF. Verifique se o LibreOffice está instalado em C:\\Program Files\\LibreOffice. Detalhes: " . $erroDetalhado);
        }

        return $caminhoPdfEsperado;
    }
}

?>