<?php
// Arquivo: painel_operador.php (VERSÃO INTEGRADA)

// 1. Inicializa o ambiente e verifica a autenticação do FusionPBX
require_once dirname(__DIR__, 2) . "/resources/require.php";
require_once "resources/check_auth.php";

// A partir daqui, o PHP já sabe que o usuário está logado.
$domain_name = $_SESSION['domain']['name']; // Pega o domínio da sessão do FusionPBX
?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Operador - <?php echo htmlspecialchars($domain_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        html, body { height: 100vh; margin: 0; overflow: hidden; }
        body { display: flex; flex-direction: column; }
        .wrapper { flex-grow: 1; display: flex; flex-direction: column; padding: 0 !important; max-width: 100%; }
        .iframe-container { flex-grow: 1; border: none; width: 100%; height: 100%; }
        /* ... outros estilos ... */
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fa-solid fa-headset me-2"></i>Painel do Operador</a>
            <div class="d-flex align-items-center">
                <span class="navbar-text me-3">Domínio: <strong><?php echo htmlspecialchars($domain_name); ?></strong></span>
                <a href="index.php" class="btn btn-outline-primary btn-sm me-3"><i class="fa-solid fa-arrow-left me-1"></i>Dashboard</a>
                </div>
        </div>
    </nav>
    
    <div class="wrapper">
        <iframe class="iframe-container" src="../app/basic_operator_panel/index.php"></iframe>
    </div>
    
    <script>
        // Cole aqui o mesmo JavaScript do seu index.php para o tema claro/escuro
    </script>
</body> 
</html>