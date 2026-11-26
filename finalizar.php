<?php
session_start();
require_once "config/conexao.php";

// Verificar se o cliente está logado

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$sucesso = false;
$pedidoId = 0;
$pagamento = "";
$total = 0;
$itens = [];

// Verificar se o carrinho está vazio

if (empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php");
    exit;
}

// Buscar os produtos do carrinho

foreach ($_SESSION['carrinho'] as $id => $quantidade) {

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);
    $produto = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($produto) {

        $produto['quantidade'] = $quantidade;
        $produto['subtotal'] = $produto['preco'] * $quantidade;

        $total += $produto['subtotal'];

        $itens[] = $produto;
    }
}

// Finalizar pedido

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pagamento = $_POST['pagamento'] ?? '';

    $formasValidas = ['PIX', 'Boleto', 'Cartão'];

    if (!in_array($pagamento, $formasValidas, true)) {

        $mensagem = "Selecione uma forma de pagamento.";

    } else {

        try {

            $conexao->beginTransaction();

            $total = 0;
            $produtosPedido = [];

            // Conferir o estoque novamente

            foreach ($_SESSION['carrinho'] as $id => $quantidade) {

                $consulta = $conexao->prepare(
                    "SELECT * FROM produtos
                     WHERE id = ? FOR UPDATE"
                );

                $consulta->execute([$id]);

                $produto = $consulta->fetch(PDO::FETCH_ASSOC);

                if (!$produto ||
                    $quantidade <= 0 ||
                    $quantidade > $produto['estoque']) {

                    throw new Exception(
                        "Um dos produtos está com estoque insuficiente."
                    );
                }

                $produto['quantidade'] = $quantidade;

                $total += $produto['preco'] * $quantidade;

                $produtosPedido[] = $produto;
            }

            if (empty($produtosPedido)) {
                throw new Exception("O carrinho está vazio.");
            }

            // Registrar o pedido

            $sql = "INSERT INTO pedidos
                    (usuario_id, forma_pagamento, total)
                    VALUES (?, ?, ?)";

            $consulta = $conexao->prepare($sql);

            $consulta->execute([
                $_SESSION['usuario_id'],
                $pagamento,
                $total
            ]);

            $pedidoId = $conexao->lastInsertId();

            // Registrar os itens do pedido

            foreach ($produtosPedido as $produto) {

                $sql = "INSERT INTO itens_pedido
                        (pedido_id, produto_id,
                         quantidade, preco_unitario)
                        VALUES (?, ?, ?, ?)";

                $consulta = $conexao->prepare($sql);

                $consulta->execute([
                    $pedidoId,
                    $produto['id'],
                    $produto['quantidade'],
                    $produto['preco']
                ]);

                // Atualizar o estoque

                $sql = "UPDATE produtos
                        SET estoque = estoque - ?
                        WHERE id = ?";

                $consulta = $conexao->prepare($sql);

                $consulta->execute([
                    $produto['quantidade'],
                    $produto['id']
                ]);
            }

            $conexao->commit();

            // Limpar o carrinho

            $_SESSION['carrinho'] = [];

            $sucesso = true;

        } catch (Exception $e) {

            if ($conexao->inTransaction()) {
                $conexao->rollBack();
            }

            $mensagem = "Não foi possível finalizar a compra.
                         Verifique o estoque e tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Finalizar compra | Serenne</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

<!-- Menu -->

<nav class="navbar navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            SERENNE
        </a>

        <a class="nav-link" href="index.php">
            Início
        </a>

    </div>
</nav>

<main class="container my-5">

    <p class="categoria-produto text-center">
        JOALHERIA SERENNE
    </p>

    <?php if ($sucesso): ?>

        <!-- Compra realizada -->

        <div class="caixa-finalizar text-center">

            <div class="icone-sucesso">
                ✓
            </div>

            <h1 class="mb-3">
                Compra realizada com sucesso!
            </h1>

            <p>
                Obrigado por escolher a Serenne,
                <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!
            </p>

            <div class="resumo-pedido my-4">

                <p>
                    <strong>Número do pedido:</strong>
                    #<?= $pedidoId ?>
                </p>

                <p>
                    <strong>Forma de pagamento:</strong>
                    <?= htmlspecialchars($pagamento) ?>
                </p>

                <h3>
                    Total: R$ <?= number_format(
                        $total, 2, ',', '.'
                    ) ?>
                </h3>

            </div>

            <p class="text-muted">
                Pagamento simulado para fins acadêmicos.
            </p>

            <a href="index.php" class="btn btn-serenne">

                Voltar à loja

            </a>

        </div>

    <?php else: ?>

        <h1 class="text-center mb-4">
            Finalizar compra
        </h1>

        <div class="caixa-finalizar">

            <p>
                Olá,
                <strong>
                    <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!
                </strong>
            </p>

            <p class="text-muted">
                Confira seu pedido e escolha a forma de pagamento.
            </p>

            <?php if ($mensagem !== ""): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($mensagem) ?>

                </div>

            <?php endif; ?>

            <!-- Resumo dos produtos -->

            <h4 class="mt-4 mb-3">
                Resumo do pedido
            </h4>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($itens as $item): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($item['nome']) ?>
                                </td>

                                <td>
                                    <?= $item['quantidade'] ?>
                                </td>

                                <td>
                                    R$ <?= number_format(
                                        $item['subtotal'], 2, ',', '.'
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="resumo-pedido text-end mb-4">

                <h3>

                    Total: R$ <?= number_format(
                        $total, 2, ',', '.'
                    ) ?>

                </h3>

            </div>

            <!-- Formas de pagamento -->

            <form method="POST">

                <h4 class="mb-3">
                    Forma de pagamento
                </h4>

                <label class="opcao-pagamento">

                    <input type="radio"
                           name="pagamento"
                           value="PIX"
                           required>

                    PIX

                </label>

                <label class="opcao-pagamento">

                    <input type="radio"
                           name="pagamento"
                           value="Boleto"
                           required>

                    Boleto bancário

                </label>

                <label class="opcao-pagamento">

                    <input type="radio"
                           name="pagamento"
                           value="Cartão"
                           required>

                    Cartão

                </label>

                <p class="text-muted small mt-3">
                    Pagamento simulado. Nenhuma cobrança será realizada.
                </p>

                <button type="submit"
                        class="btn btn-serenne w-100 mt-3">

                    Confirmar compra

                </button>

            </form>

            <a href="carrinho.php"
               class="btn btn-outline-serenne w-100 mt-3">

                Voltar ao carrinho

            </a>

        </div>

    <?php endif; ?>

</main>

<!-- Rodapé -->

<footer>

    <h4>SERENNE</h4>

    <p>Elegância em cada detalhe.</p>

    <p>© 2026 Serenne Joalheria.</p>

</footer>
<script src="script.js"></script>

</body>
</html>