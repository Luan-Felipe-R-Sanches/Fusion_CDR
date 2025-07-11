<?php
// Arquivo: download.php
session_start();

// Segurança: Garante que o usuário está logado
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("HTTP/1.1 403 Forbidden");
    exit("Acesso negado.");
}

// Segurança: Garante que um ID foi fornecido
$uuid = $_GET['id'] ?? null;
if (!$uuid) {
    header("HTTP/1.1 400 Bad Request");
    exit("ID da chamada nao fornecido.");
}

$dbconn = require 'db_connect.php';
$domain_name = $_SESSION['domain_name'];

// Busca no banco os detalhes da gravação, garantindo que pertence ao domínio do usuário
$sql = "SELECT record_path, record_name FROM v_xml_cdr WHERE xml_cdr_uuid = $1 AND domain_name = $2";
$result = pg_query_params($dbconn, $sql, [$uuid, $domain_name]);
$recording = pg_fetch_assoc($result);

if (!$recording || empty($recording['record_path']) || empty($recording['record_name'])) {
    header("HTTP/1.1 404 Not Found");
    exit("Gravacao nao encontrada ou acesso negado.");
}

// Monta o caminho completo do arquivo no servidor
$file_path = rtrim($recording['record_path'], '/') . '/' . $recording['record_name'];

// Verifica se o arquivo realmente existe no servidor
if (!file_exists($file_path)) {
    header("HTTP/1.1 404 Not Found");
    exit("Arquivo de gravacao nao existe no servidor: " . htmlspecialchars($file_path));
}

// Determina o tipo do arquivo e envia os cabeçalhos corretos
$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$content_type = 'application/octet-stream'; // Tipo padrão
if ($file_extension == 'wav') {
    $content_type = 'audio/wav';
} elseif ($file_extension == 'mp3') {
    $content_type = 'audio/mpeg';
}

header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($file_path));
header('Accept-Ranges: bytes');

// Limpa o buffer de saída e envia o arquivo
ob_clean();
flush();
readfile($file_path);
exit;