<?php

namespace App;

class RedmineAPI
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * Busca os dados de uma issue específica pelo ID.
     * 
     * @param int|string $issueId
     * @return array
     * @throws \Exception
     */
    public function buscarIssue($issueId): array
    {
        $url = "{$this->baseUrl}/issues/{$issueId}.json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Redmine-API-Key: {$this->apiKey}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // Desativa verificação SSL estrita apenas se houver problemas com certificados corporativos internos
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception("Erro de conexão ao comunicar com o Redmine: {$curlError}");
        }

        if ($httpCode === 401 || $httpCode === 403) {
            throw new \Exception("Acesso negado ao Redmine. Verifique a sua API Key.");
        }

        if ($httpCode === 404) {
            throw new \Exception("A issue #{$issueId} não foi encontrada no Redmine.");
        }

        if ($httpCode !== 200) {
            throw new \Exception("O Redmine retornou um erro (Código HTTP: {$httpCode}).");
        }

        $dados = json_decode($response, true);

        if (!isset($dados['issue'])) {
            throw new \Exception("Resposta inválida recebida da API do Redmine para a issue #{$issueId}.");
        }

        return $dados['issue'];
    }
}