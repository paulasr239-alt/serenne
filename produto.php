<?php
session_start();
require_once "config/conexao.php";

$id = (int) ($_GET['id'] ?? 0);

$sql = "SELECT produtos.*, categorias.nome AS categoria
        FROM produtos
        INNER JOIN categorias
        ON produtos.categoria_id = categorias.id
        WHERE produtos.id = ?";

$consulta = $conexao->prepare($sql);
$consulta->execute([$id]);

$produto = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($produto['nome']) ?> | Serenne</title>

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

        <div class="d-flex gap-3">

            <a class="nav-link" href="index.php">
                Início
            </a>

            <a class="nav-link" href="carrinho.php">
                Carrinho
            </a>

        </div>

    </div>
</nav>

<!-- Detalhes do produto -->

<main class="container my-5">

    <a href="index.php" class="btn btn-outline-serenne mb-4">
        Voltar à coleção
    </a>

    <div class="row align-items-center g-5">

        <!-- Foto -->

        <div class="col-md-6">

            <img src="img/<?= htmlspecialchars($produto['imagem']) ?>"
                 class="foto-produto"
                 alt="<?= htmlspecialchars($produto['nome']) ?>">

        </div>

        <!-- Informações -->

        <div class="col-md-6">

            <p class="categoria-produto">
                <?= htmlspecialchars($produto['categoria']) ?>
            </p>

            <h1>
                <?= htmlspecialchars($produto['nome']) ?>
            </h1>

            <p class="descricao-produto mt-3">
                <?= htmlspecialchars($produto['descricao']) ?>
            </p>

            <h2 class="preco-produto my-4">

                R$ <?= number_format(
                    $produto['preco'], 2, ',', '.'
                ) ?>

            </h2>

            <?php if ($produto['estoque'] > 0): ?>

                <p class="estoque-produto">

                    Disponível em estoque:
                    <?= $produto['estoque'] ?> unidades

                </p>

                <form method="POST" action="carrinho.php">

                    <input type="hidden"
                           name="produto_id"
                           value="<?= $produto['id'] ?>">

                    <label class="form-label">
                        Quantidade
                    </label>

                    <input type="number"
                           name="quantidade"
                           class="form-control quantidade-produto mb-4"
                           value="1"
                           min="1"
                           max="<?= $produto['estoque'] ?>"
                           required>

                    <button type="submit"
                            name="adicionar"
                            class="btn btn-serenne w-100">

                        Adicionar ao carrinho

                    </button>

                </form>

            <?php else: ?>

                <p class="text-danger">
                    Produto indisponível no momento.
                </p>

                <button class="btn btn-secondary w-100" disabled>
                    Produto esgotado
                </button>

            <?php endif; ?>

        </div>

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