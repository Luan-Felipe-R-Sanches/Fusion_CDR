<?php
// Arquivo: auth.php
session_start();

$domain = $_POST['domain'] ?? '';
$password = $_POST['password'] ?? '';

// ATENÇÃO: LÓGICA DE SENHA FIXA (INSEGURO!)
$senha_fixa = 'N3tware385br##@@';

// Validação (simples, mas funcional para o protótipo)
if (!empty($domain) && $password === $senha_fixa) {
    // Login bem-sucedido
    $_SESSION['loggedin'] = true;
    $_SESSION['domain_name'] = $domain;
    
    // Redireciona para o dashboard
    header('Location: index.php');
    exit;
} else {
    // Falha no login
    header('Location: login.php?error=1');
    exit;
}