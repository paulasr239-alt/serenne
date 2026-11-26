<?php
session_start();
require_once "config/conexao.php";

$busca = trim($_GET['busca'] ?? '');
$categoria = (int) ($_GET['categoria'] ?? 0);

$categorias = $conexao->query(
    "SELECT * FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM produtos WHERE 1=1";
$parametros = [];

if ($busca !== '') {
    $sql .= " AND nome LIKE ?";
    $parametros[] = "%$busca%";
}

if ($categoria > 0) {
    $sql .= " AND categoria_id = ?";
    $parametros[] = $categoria;
}

$sql .= " ORDER BY id";

$consulta = $conexao->prepare($sql);
$consulta->execute($parametros);
$produtos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Serenne | Joalheria</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

<!-- Menu -->

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            SERENNE
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menu">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        Início
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#colecao">
                        Coleção
                    </a>
                </li>

                <?php if (isset($_SESSION['usuario_id'])): ?>

                    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>

                        <li class="nav-item">
                            <a class="nav-link" href="admin/index.php">
                                Administração
                            </a>
                        </li>

                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            Sair
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            Entrar
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="cadastro.php">
                            Cadastro
                        </a>
                    </li>

                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="carrinho.php">
                        Carrinho
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- Banner -->

<section class="banner">

    <span>JOALHERIA SERENNE</span>

    <h1 class="mt-3">
        Elegância em cada detalhe.
    </h1>

    <p>
        Joias que tornam momentos inesquecíveis.
    </p>

    <a href="#colecao" class="btn btn-dourado mt-3">
        Conheça nossa coleção
    </a>

</section>

<!-- Vitrine -->

<main class="container" id="colecao">

    <h2 class="titulo-secao">
        Nossa coleção
    </h2>

    <!-- Pesquisa e categorias -->

    <form method="GET" class="row g-3 mb-5">

        <div class="col-md-6">

            <input type="text"
                   name="busca"
                   class="form-control"
                   placeholder="Buscar uma joia..."
                   value="<?= htmlspecialchars($busca) ?>">

        </div>

        <div class="col-md-4">

            <select name="categoria" class="form-select">

                <option value="0">
                    Todas as categorias
                </option>

                <?php foreach ($categorias as $cat): ?>

                    <option value="<?= $cat['id'] ?>"
                        <?= $categoria == $cat['id'] ? 'selected' : '' ?>>

                        <?= htmlspecialchars($cat['nome']) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <div class="col-md-2">

            <button type="submit"
                    class="btn btn-serenne w-100">
                Buscar
            </button>

        </div>

    </form>

    <!-- Produtos -->

    <div class="row g-4">

        <?php if (empty($produtos)): ?>

            <p class="text-center">
                Nenhum produto encontrado.
            </p>

        <?php endif; ?>

        <?php foreach ($produtos as $produto): ?>

            <div class="col-12 col-sm-6 col-lg-3">

                <div class="card h-100">

                    <img src="img/<?= htmlspecialchars($produto['imagem']) ?>"
                         class="card-img-top"
                         alt="<?= htmlspecialchars($produto['nome']) ?>">

                    <div class="card-body text-center d-flex flex-column">

                        <h5 class="card-title">
                            <?= htmlspecialchars($produto['nome']) ?>
                        </h5>

                        <p class="preco">
                            R$ <?= number_format(
                                $produto['preco'], 2, ',', '.'
                            ) ?>
                        </p>

                        <a href="produto.php?id=<?= $produto['id'] ?>"
                           class="btn btn-serenne w-100 mt-auto">

                            Ver detalhes

                        </a>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</main>

<!-- Rodapé -->

<footer>

    <h4>SERENNE</h4>

    <p>Elegância em cada detalhe.</p>

    <p>© 2026 Serenne Joalheria.</p>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

<script src="script.js"></script>

</body>
</html>