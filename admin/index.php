<?php
session_start();

if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Administração | Serenne</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="../css/estilo.css">
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            SERENNE | ADMIN
        </a>

        <div class="d-flex gap-3">

            <a class="nav-link" href="../index.php">
                Loja
            </a>

            <a class="nav-link" href="../logout.php">
                Sair
            </a>

        </div>

    </div>
</nav>

<main class="container my-5">

    <p class="categoria-produto text-center">
        ADMINISTRAÇÃO SERENNE
    </p>

    <h1 class="text-center mb-3">
        Painel administrativo
    </h1>

    <p class="text-center mb-5">
        Bem-vindo,
        <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!
    </p>

    <div class="row g-4 justify-content-center">

        <div class="col-md-5">

            <div class="caixa-admin text-center h-100">

                <h3>Gerenciar produtos</h3>

                <p class="my-4">
                    Cadastre, edite e exclua joias da coleção.
                    Controle os preços e o estoque.
                </p>

                <a href="produtos.php"
                   class="btn btn-serenne w-100">

                    Acessar produtos

                </a>

            </div>

        </div>

        <div class="col-md-5">

            <div class="caixa-admin text-center h-100">

                <h3>Gerenciar pedidos</h3>

                <p class="my-4">
                    Consulte os pedidos realizados
                    pelos clientes da Serenne.
                </p>

                <a href="pedidos.php"
                   class="btn btn-serenne w-100">

                    Visualizar pedidos

                </a>

            </div>

        </div>

    </div>

</main>

<footer>

    <h4>SERENNE</h4>

    <p>Painel administrativo</p>

    <p>© 2026 Serenne Joalheria.</p>

</footer>
<script src="../script.js"></script>

</body>
</html>