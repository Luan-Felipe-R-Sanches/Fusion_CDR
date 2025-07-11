<?php
// Arquivo: get_cdr_data.php
// Este script busca os dados e os retorna como um array.

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Se não estiver logado, retorna um array vazio.
    return [];
}

$dbconn = require 'db_connect.php';
$domain_name = $_SESSION['domain_name'];

// --- LÓGICA DOS FILTROS (a mesma do dashboard) ---
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-7 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$direcao_filtro = $_GET['direcao'] ?? 'todas';

// --- CONSTRUÇÃO DA CONSULTA DINÂMICA (a mesma do dashboard) ---
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
if ($direcao_filtro !== 'todas' && !empty($direcao_filtro)) {
    $conditions[] = "direction = $" . (count($params) + 1);
    $params[] = $direcao_filtro;
}

$where_clause = "WHERE domain_name = $1";
if (count($conditions) > 0) {
    $where_clause .= " AND " . implode(" AND ", $conditions);
}

// --- CONSULTA PRINCIPAL PARA A TABELA ---
$sql = "SELECT start_stamp, caller_id_number, destination_number, billsec, direction, status, hangup_cause FROM v_xml_cdr {$where_clause} ORDER BY start_stamp DESC;";
$result = pg_query_params($dbconn, $sql, $params);

$data_rows = [];
while ($row = pg_fetch_assoc($result)) {
    $data_rows[] = $row;
}

pg_close($dbconn);

// Retorna o array de dados para quem o incluiu.
return $data_rows;