<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Relatório CDR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body, html {
            height: 100%;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            /* Fundo com gradiente de azul claro */
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        .login-card {
            width: 100%;
            max-width: 450px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
            border: none;
        }
        .login-card .card-header {
            background-color: transparent;
            border-bottom: none;
            text-align: center;
            padding-top: 2rem;
        }
        .login-card .logo-icon {
            font-size: 3.5rem;
            /* Tom principal de azul para o ícone */
            color: #3c8ce7;
        }
        .btn-primary {
            /* Botão no mesmo tom de azul */
            background-color: #3c8ce7;
            border-color: #3c8ce7;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            /* Tom mais escuro para o efeito hover */
            background-color: #306eb5;
            border-color: #306eb5;
        }
        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="card login-card">
        <div class="card-header">
            <i class="fa-solid fa-chart-line logo-icon"></i>
            <h4 class="mt-3">Relatório de Chamadas</h4>
            <p class="text-muted">Faça login para continuar</p>
        </div>
        <div class="card-body px-4 pb-4">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">Domínio ou senha inválidos.</div>
            <?php endif; ?>
            <form action="auth.php" method="post">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="fa-solid fa-globe"></i></span>
                    <div class="form-floating">
                        <input type="text" class="form-control" id="domain" name="domain" placeholder="Domínio" required>
                        <label for="domain">Domínio</label>
                    </div>
                </div>
                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                        <label for="password">Senha</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>