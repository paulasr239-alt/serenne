<?php
session_start();
require_once "config/conexao.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$mensagem = "";

// Adicionar produto

if (isset($_POST['adicionar'])) {

    $id = (int) $_POST['produto_id'];
    $quantidade = (int) $_POST['quantidade'];

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);
    $produto = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($produto && $quantidade > 0) {

        $quantidadeAtual = $_SESSION['carrinho'][$id] ?? 0;
        $novaQuantidade = $quantidadeAtual + $quantidade;

        if ($novaQuantidade <= $produto['estoque']) {

            $_SESSION['carrinho'][$id] = $novaQuantidade;

            $mensagem = "Joia adicionada ao carrinho!";

        } else {

            $mensagem = "Quantidade superior ao estoque disponível.";

        }
    }
}

// Atualizar quantidade

if (isset($_POST['atualizar'])) {

    $id = (int) $_POST['produto_id'];
    $quantidade = (int) $_POST['quantidade'];

    $consulta = $conexao->prepare(
        "SELECT estoque FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);
    $produto = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($produto &&
        isset($_SESSION['carrinho'][$id]) &&
        $quantidade > 0 &&
        $quantidade <= $produto['estoque']) {

        $_SESSION['carrinho'][$id] = $quantidade;

        $mensagem = "Quantidade atualizada!";

    } else {

        $mensagem = "Quantidade inválida ou estoque insuficiente.";

    }
}

// Remover produto

if (isset($_POST['remover'])) {

    $id = (int) $_POST['produto_id'];

    unset($_SESSION['carrinho'][$id]);

    $mensagem = "Produto removido do carrinho.";

}

// Buscar produtos do carrinho

$itens = [];
$total = 0;
$estoqueValido = true;

foreach ($_SESSION['carrinho'] as $id => $quantidade) {

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);

    $produto = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($produto) {

        $produto['quantidade'] = $quantidade;

        $produto['subtotal'] =
            $produto['preco'] * $quantidade;

        $total += $produto['subtotal'];

        if ($quantidade > $produto['estoque']) {
            $estoqueValido = false;
        }

        $itens[] = $produto;

    } else {

        unset($_SESSION['carrinho'][$id]);

    }
}

// Evitar adicionar novamente ao atualizar a página

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_SESSION['mensagem_carrinho'] = $mensagem;

    header("Location: carrinho.php");
    exit;
}

$mensagem = $_SESSION['mensagem_carrinho'] ?? "";
unset($_SESSION['mensagem_carrinho']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Carrinho | Serenne</title>

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

<!-- Carrinho -->

<main class="container my-5">

    <p class="categoria-produto text-center">
        JOALHERIA SERENNE
    </p>

    <h1 class="text-center mb-4">
        Meu carrinho
    </h1>

    <?php if ($mensagem !== ""): ?>

        <div class="alert alerta-serenne">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <div class="caixa-carrinho">

        <?php if (empty($itens)): ?>

            <div class="text-center py-5">

                <h3>Seu carrinho está vazio.</h3>

                <p class="text-muted">
                    Encontre a joia perfeita em nossa coleção.
                </p>

                <a href="index.php"
                   class="btn btn-serenne mt-3">

                    Conhecer coleção

                </a>

            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>

                        <tr>
                            <th>Produto</th>
                            <th>Preço</th>
                            <th>Quantidade</th>
                            <th>Subtotal</th>
                            <th>Ações</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($itens as $item): ?>

                        <tr>

                            <td>

                                <div class="d-flex align-items-center gap-3">

                                    <img src="img/<?= htmlspecialchars($item['imagem']) ?>"
                                         class="foto-carrinho"
                                         alt="<?= htmlspecialchars($item['nome']) ?>">

                                    <div>

                                        <strong>
                                            <?= htmlspecialchars($item['nome']) ?>
                                        </strong>

                                        <?php if ($item['quantidade'] > $item['estoque']): ?>

                                            <p class="text-danger small mb-0">
                                                Estoque insuficiente.
                                            </p>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>

                            <td>

                                R$ <?= number_format(
                                    $item['preco'], 2, ',', '.'
                                ) ?>

                            </td>

                            <td>

                                <form method="POST"
                                      class="d-flex gap-2">

                                    <input type="hidden"
                                           name="produto_id"
                                           value="<?= $item['id'] ?>">

                                    <input type="number"
                                           name="quantidade"
                                           class="form-control quantidade-carrinho"
                                           value="<?= $item['quantidade'] ?>"
                                           min="1"
                                           max="<?= $item['estoque'] ?>"
                                           required>

                                    <button type="submit"
                                            name="atualizar"
                                            class="btn btn-outline-serenne btn-sm">

                                        Atualizar

                                    </button>

                                </form>

                            </td>

                            <td>

                                R$ <?= number_format(
                                    $item['subtotal'], 2, ',', '.'
                                ) ?>

                            </td>

                            <td>

                                <form method="POST">

                                    <input type="hidden"
                                           name="produto_id"
                                           value="<?= $item['id'] ?>">

                                    <button type="submit"
                                            name="remover"
                                            class="btn btn-remover btn-sm">

                                        Remover

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <!-- Total -->

            <div class="resumo-carrinho">

                <h3>

                    Total: R$ <?= number_format(
                        $total, 2, ',', '.'
                    ) ?>

                </h3>

                <div class="d-flex flex-wrap justify-content-end gap-3 mt-4">

                    <a href="index.php"
                       class="btn btn-outline-serenne">

                        Continuar comprando

                    </a>

                    <?php if ($estoqueValido): ?>

                        <a href="finalizar.php"
                           class="btn btn-serenne">

                            Finalizar compra

                        </a>

                    <?php else: ?>

                        <button class="btn btn-secondary" disabled>
                            Finalizar compra
                        </button>

                    <?php endif; ?>

                </div>

                <?php if (!$estoqueValido): ?>

                    <p class="text-danger mt-3">
                        Ajuste ou remova os produtos com estoque insuficiente.
                    </p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

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