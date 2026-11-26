<?php
session_start();
require_once "../config/conexao.php";

if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT pedidos.*,
               usuarios.nome AS cliente,
               usuarios.email
        FROM pedidos
        INNER JOIN usuarios
        ON pedidos.usuario_id = usuarios.id
        ORDER BY pedidos.id DESC";

$pedidos = $conexao->query($sql)
                   ->fetchAll(PDO::FETCH_ASSOC);

$pedidoSelecionado = null;
$itens = [];

// Visualizar detalhes do pedido

if (isset($_GET['pedido'])) {

    $id = (int) $_GET['pedido'];

    $consulta = $conexao->prepare(
        "SELECT pedidos.*, usuarios.nome AS cliente
         FROM pedidos
         INNER JOIN usuarios
         ON pedidos.usuario_id = usuarios.id
         WHERE pedidos.id = ?"
    );

    $consulta->execute([$id]);

    $pedidoSelecionado = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($pedidoSelecionado) {

        $sql = "SELECT itens_pedido.*,
                       produtos.nome AS produto
                FROM itens_pedido
                INNER JOIN produtos
                ON itens_pedido.produto_id = produtos.id
                WHERE itens_pedido.pedido_id = ?";

        $consulta = $conexao->prepare($sql);

        $consulta->execute([$id]);

        $itens = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Pedidos | Serenne Admin</title>

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

            <a class="nav-link" href="index.php">
                Painel
            </a>

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

    <h1 class="text-center mb-4">
        Pedidos realizados
    </h1>

    <div class="caixa-admin mb-4">

        <?php if (empty($pedidos)): ?>

            <p class="text-center">
                Nenhum pedido realizado até o momento.
            </p>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>Pedido</th>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Pagamento</th>
                            <th>Total</th>
                            <th>Detalhes</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($pedidos as $pedido): ?>

                            <tr>

                                <td>
                                    #<?= $pedido['id'] ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($pedido['cliente']) ?>
                                </td>

                                <td>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($pedido['data_pedido'])
                                    ) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($pedido['forma_pagamento']) ?>

                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $pedido['total'], 2, ',', '.'
                                    ) ?>

                                </td>

                                <td>

                                    <a href="?pedido=<?= $pedido['id'] ?>"
                                       class="btn btn-outline-serenne btn-sm">

                                        Ver detalhes

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

    <!-- Detalhes do pedido -->

    <?php if ($pedidoSelecionado): ?>

        <div class="caixa-admin">

            <h3 class="mb-4">

                Detalhes do pedido
                #<?= $pedidoSelecionado['id'] ?>

            </h3>

            <p>

                <strong>Cliente:</strong>

                <?= htmlspecialchars($pedidoSelecionado['cliente']) ?>

            </p>

            <p>

                <strong>Pagamento:</strong>

                <?= htmlspecialchars($pedidoSelecionado['forma_pagamento']) ?>

            </p>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Preço unitário</th>
                            <th>Subtotal</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($itens as $item): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($item['produto']) ?>
                                </td>

                                <td>
                                    <?= $item['quantidade'] ?>
                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $item['preco_unitario'], 2, ',', '.'
                                    ) ?>

                                </td>

                                <td>

                                    R$ <?= number_format(
                                        $item['quantidade'] *
                                        $item['preco_unitario'],
                                        2, ',', '.'
                                    ) ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="resumo-pedido text-end">

                <h3>

                    Total: R$ <?= number_format(
                        $pedidoSelecionado['total'], 2, ',', '.'
                    ) ?>

                </h3>

            </div>

            <a href="pedidos.php"
               class="btn btn-outline-serenne mt-4">

                Fechar detalhes

            </a>

        </div>

    <?php endif; ?>

</main>

<footer>

    <h4>SERENNE</h4>

    <p>Painel administrativo</p>

    <p>© 2026 Serenne Joalheria.</p>

</footer>
<script src="../script.js"></script>

</body>
</html>