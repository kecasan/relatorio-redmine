<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/config.php';

use App\RedmineAPI;

$erro = null;
$sucesso = null;
$issuesList = [];
$tipoRelatorio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoRelatorio = filter_input(INPUT_POST, 'tipo_relatorio', FILTER_SANITIZE_SPECIAL_CHARS);
    $issuesInput = filter_input(INPUT_POST, 'issues', FILTER_SANITIZE_SPECIAL_CHARS);

    if (!in_array($tipoRelatorio, ['planejamento', 'encerramento'])) {
        $erro = "Por favor, selecione um tipo de relatório válido.";
    } elseif (empty(trim($issuesInput))) {
        $erro = "Informe ao menos um ID de issue do Redmine.";
    } else {
        preg_match_all('/\d+/', $issuesInput, $matches);
        $issuesIds = array_unique($matches[0]);

        if (empty($issuesIds)) {
            $erro = "Nenhum ID de issue válido (numérico) foi localizado.";
        } else {
            try {
                $api = new RedmineAPI($config['redmine']['url'], $config['redmine']['api_key']);
                
                // Consulta todas as issues informadas
                foreach ($issuesIds as $id) {
                    $dados = $api->buscarIssue($id);
                    if ($dados) {
                        $issuesList[] = $dados;
                    }
                }

                if (!empty($issuesList)) {
                    $sucesso = count($issuesList) . " issue(s) consultada(s) com sucesso no Redmine!";
                } else {
                    $erro = "Não foi possível retornar dados para as issues informadas.";
                }                
            } catch (\Exception $e) {
                $erro = $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Relatórios de Sprint - Redmine</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Relatório de Sprint - Redmine</h1>

        <?php if ($erro): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Consulta -->
        <form action="index.php" method="POST">
            <div class="form-group">
                <label for="tipo_relatorio">Tipo de Relatório:</label>
                <select name="tipo_relatorio" id="tipo_relatorio" required>
                    <option value="">-- Selecione --</option>
                    <option value="planejamento" <?= ($tipoRelatorio === 'planejamento') ? 'selected' : '' ?>>Planejamento da Sprint</option>
                    <option value="encerramento" <?= ($tipoRelatorio === 'encerramento') ? 'selected' : '' ?>>Encerramento da Sprint</option>
                </select>
            </div>

            <div class="form-group">
                <label for="issues">IDs das Issues do Redmine:</label>
                <textarea name="issues" id="issues" placeholder="Exemplo: 47993, 47994" required><?= htmlspecialchars($_POST['issues'] ?? '') ?></textarea>
            </div>

            <button type="submit">Consultar Redmine</button>
        </form>

        <!-- Formulário de Edição e Revisão dos Dados -->
        <?php if (!empty($issuesList)): ?>
            <hr>
            <h2>Revisão e Complementação dos Dados</h2>
            <form action="preview.php" method="POST">
                <input type="hidden" name="tipo_relatorio" value="<?= htmlspecialchars($tipoRelatorio) ?>">

                <!-- BLOCO ADICIONADO: Dados Gerais do Projeto -->
                <div class="card" style="margin-bottom: 20px;">
                    <h3>Identificação do Projeto e Sprint</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label>Contrato:</label>
                            <input type="text" name="contrato" placeholder="Ex: 05/2024" style="width: 100%;">
                        </div>
                        <div>
                            <label>Número da OS:</label>
                            <input type="text" name="numero_os" placeholder="Ex: OS-012" style="width: 100%;">
                        </div>
                        <div>
                            <label>Nome do Projeto:</label>
                            <input type="text" name="nome_projeto" placeholder="Ex: Sistema SAPE" style="width: 100%;">
                        </div>
                        <div>
                            <label>Processo SEI:</label>
                            <input type="text" name="processo_sei" placeholder="Ex: 23524.000000/2026-00" style="width: 100%;">
                        </div>
                        <div>
                            <label>SAPE:</label>
                            <input type="text" name="sape" placeholder="Ex: SAPE-123" style="width: 100%;">
                        </div>
                        <div>
                            <label>Product Owner:</label>
                            <input type="text" name="product_owner" placeholder="Nome do PO" style="width: 100%;">
                        </div>
                        <div>
                            <label>Líder do Projeto:</label>
                            <input type="text" name="lider_projeto" placeholder="Nome do Líder" style="width: 100%;">
                        </div>
                        <div>
                            <label>Número da Sprint:</label>
                            <input type="text" name="numero_sprint" placeholder="Ex: Sprint 05" style="width: 100%;">
                        </div>
                        <div>
                            <label>Período da Sprint:</label>
                            <input type="text" name="periodo_sprint" placeholder="Ex: 01/09/2026 a 15/09/2026" style="width: 100%;">
                        </div>
                    </div>
                </div>

                <h3>Demandas da Sprint</h3>
                <table class="table" border="1" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Assunto / Descrição</th>
                            <th>Responsável</th>
                            <th>Status</th>
                            <th>PF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($issuesList as $index => $issue): ?>
                            <tr>
                                <td>
                                    <strong>#<?= htmlspecialchars($issue['id']) ?></strong>
                                    <input type="hidden" name="demandas[<?= $index ?>][id]" value="<?= htmlspecialchars($issue['id']) ?>">
                                </td>
                                <td>
                                    <input type="text" name="demandas[<?= $index ?>][descricao]" value="<?= htmlspecialchars($issue['subject'] ?? '') ?>" style="width: 95%;">
                                </td>
                                <td>
                                    <input type="text" name="demandas[<?= $index ?>][responsavel]" value="<?= htmlspecialchars($issue['assigned_to']['name'] ?? '') ?>">
                                </td>
                                <td>
                                    <input type="text" name="demandas[<?= $index ?>][situacao]" value="<?= htmlspecialchars($issue['status']['name'] ?? '') ?>">
                                </td>
                                <td>
                                    <input type="text" name="demandas[<?= $index ?>][pf]" placeholder="PF" style="width: 50px;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit">Gerar Prévia do Documento</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>