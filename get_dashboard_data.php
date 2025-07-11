<?php
// Arquivo: get_dashboard_data.php (VERSÃO COM DADOS MELHORADOS PARA VISUALIZAÇÃO)
ini_set('display_errors', 1); error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(403); echo json_encode(['error' => 'Acesso negado']); exit;
}

$dbconn = require 'db_connect.php';
$domain_name = $_SESSION['domain_name'];

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

$params = [$domain_name];
$conditions = [];
if (!empty($data_inicio)) {
    $conditions[] = "start_stamp >= $" . (count($params) + 1);
    $params[] = $data_inicio . " 00:00:00";
}
if (!empty($data_fim)) {
    $conditions[] = "start_stamp <= $" . (count($params) + 1);
    $params[] = $data_fim . " 23:59:59";
}

$where_clause = "WHERE domain_name = $1";
if (count($conditions) > 0) { $where_clause .= " AND " . implode(" AND ", $conditions); }

// Consultas SQL (idênticas à versão anterior)
$sql_summary = "SELECT COUNT(*) AS total_chamadas, COALESCE(SUM(billsec), 0) AS total_duracao_segundos, COUNT(*) FILTER (WHERE status = 'answered') AS chamadas_atendidas, COUNT(*) FILTER (WHERE status = 'missed' OR status = 'no_answer') AS chamadas_perdidas, COUNT(*) FILTER (WHERE status = 'voicemail') AS chamadas_voicemail, COUNT(*) FILTER (WHERE status = 'busy') AS chamadas_ocupado FROM v_xml_cdr {$where_clause}";
$summary_result = pg_query_params($dbconn, $sql_summary, $params);
$summary_data = pg_fetch_assoc($summary_result);

$sql_status_chart = "SELECT status, COUNT(*) as count FROM v_xml_cdr {$where_clause} GROUP BY status HAVING COUNT(*) > 0";
$status_result = pg_query_params($dbconn, $sql_status_chart, $params);
$status_chart_data = pg_fetch_all($status_result) ?: [];

$sql_daily_chart = "SELECT to_char(start_stamp, 'YYYY-MM-DD') as call_date, COUNT(*) as count FROM v_xml_cdr {$where_clause} GROUP BY 1 ORDER BY 1 ASC";
$daily_result = pg_query_params($dbconn, $sql_daily_chart, $params);
$daily_chart_data = pg_fetch_all($daily_result) ?: [];

pg_close($dbconn);

// NOVO: Melhora a visualização se houver apenas 1 dia de dados
if (count($daily_chart_data) === 1) {
    try {
        $single_date = new DateTime($daily_chart_data[0]['call_date']);
        $prev_date = (clone $single_date)->modify('-1 day')->format('Y-m-d');
        // Adiciona um dia "fantasma" antes para dar contexto ao gráfico de barras
        array_unshift($daily_chart_data, ['call_date' => $prev_date, 'count' => '0']);
    } catch (Exception $e) { /* ignora se a data for inválida */ }
}

$response = [
    'summary' => $summary_data,
    'statusChart' => $status_chart_data,
    'dailyChart' => $daily_chart_data
];

echo json_encode($response);