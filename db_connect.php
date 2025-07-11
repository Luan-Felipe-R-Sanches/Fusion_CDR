<?php
// Arquivo: db_connect.php

$host = '127.0.0.1'; // conforme seu config, você usou 127.0.0.1 e não localhost (ok usar qualquer um)
$port = '5432';
$dbname = 'fusionpbx';
$user = 'fusionpbx'; // usuário correto do banco
$password = 'pEzw4fNLSwAqNCNgX3qRXllNg'; // senha correta conforme seu config

$conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";

$dbconn = pg_connect($conn_string);

if (!$dbconn) {
    die("Erro: Não foi possível conectar ao PostgreSQL.");
}

// Retorna a conexão para ser usada em outros scripts
return $dbconn;
