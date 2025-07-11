<?php
// Arquivo: export_csv.php (VERSÃO ATUALIZADA)

// 1. Busca os dados já filtrados
$data = require 'get_cdr_data.php';

// Se não houver dados (ex: não logado), para a execução.
if (empty($data)) {
    exit('Acesso negado ou nenhum dado encontrado.');
}

// 2. Prepara o arquivo CSV para download
$filename = "relatorio_cdr_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// 3. Escreve o cabeçalho
fputcsv($output, ['Data e Hora', 'Origem', 'Destino', 'Duracao (H:M:S)', 'Direcao', 'Status']);

// Inclui as mesmas funções de formatação do dashboard
require_once 'helpers.php'; // Vamos criar este arquivo no próximo passo

// 4. Escreve os dados no arquivo
foreach ($data as $row) {
    $status_info = get_call_status($row);
    $direction_text = ucfirst($row['direction']);
    if ($row['direction'] == 'inbound') $direction_text = 'Entrada';
    if ($row['direction'] == 'outbound') $direction_text = 'Saída';

    $csv_row = [
        date('d/m/Y H:i:s', strtotime($row['start_stamp'])),
        $row['caller_id_number'],
        $row['destination_number'],
        format_seconds($row['billsec']),
        $direction_text,
        $status_info['text']
    ];
    fputcsv($output, $csv_row);
}

fclose($output);