<?php

class Relatorio {
    /**
     * Mapeia e padroniza os dados vindos do JSON do Redmine
     */
    public static function formatarIssue(array $issueData): array {
        $issue = $issueData['issue'] ?? [];

        // Extrai campos personalizados se existirem
        $customFields = [];
        if (isset($issue['custom_fields']) && is_array($issue['custom_fields'])) {
            foreach ($issue['custom_fields'] as $field) {
                $customFields[$field['name']] = $field['value'] ?? '';
            }
        }

        return [
            'id' => $issue['id'] ?? '',
            'sape_demanda' => $customFields['SAPE'] ?? $customFields['sape'] ?? '',
            'descricao' => $issue['subject'] ?? '',
            'projeto' => $issue['project']['name'] ?? '',
            'situacao' => $issue['status']['name'] ?? '',
            'responsavel' => $issue['assigned_to']['name'] ?? '',
            'autor' => $issue['author']['name'] ?? '',
            'horas_estimadas' => $issue['estimated_hours'] ?? 0,
            'horas_gastas' => $issue['spend_hours'] ?? 0,
            'pf' => $customFields['Pontos de Função'] ?? $customFields['PF'] ?? '',
            'fase' => $customFields['Fase'] ?? '',
            'origem' => 'api' // Identifica que o dado veio da API
        ]
    }
}

?>