<?php
session_start();
require_once "../config/conexao.php";

if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensagem = "";
$editar = null;

// Proteção dos formulários administrativos

if (empty($_SESSION['csrf_admin'])) {
    $_SESSION['csrf_admin'] = bin2hex(random_bytes(32));
}

$token = $_SESSION['csrf_admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($token, $_POST['token'] ?? '')) {
        die("Solicitação inválida.");
    }
}

// Cadastrar ou atualizar produto

if (isset($_POST['salvar'])) {

    $id = (int) ($_POST['id'] ?? 0);
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $categoria = (int) $_POST['categoria_id'];
    $preco = (float) $_POST['preco'];
    $estoque = (int) $_POST['estoque'];
    $imagem = basename(trim($_POST['imagem']));

    if ($nome === "" ||
        $descricao === "" ||
        $categoria <= 0 ||
        $preco <= 0 ||
        $estoque < 0 ||
        $imagem === "") {

        $mensagem = "Preencha os campos corretamente.";

    } else {

        if ($id > 0) {

            $sql = "UPDATE produtos SET
                    nome = ?,
                    descricao = ?,
                    categoria_id = ?,
                    preco = ?,
                    estoque = ?,
                    imagem = ?
                    WHERE id = ?";

            $consulta = $conexao->prepare($sql);

            $consulta->execute([
                $nome,
                $descricao,
                $categoria,
                $preco,
                $estoque,
                $imagem,
                $id
            ]);

            $mensagem = "Produto atualizado com sucesso!";

        } else {

            $sql = "INSERT INTO produtos
                    (nome, descricao, categoria_id,
                     preco, estoque, imagem)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $consulta = $conexao->prepare($sql);

            $consulta->execute([
                $nome,
                $descricao,
                $categoria,
                $preco,
                $estoque,
                $imagem
            ]);

            $mensagem = "Produto cadastrado com sucesso!";
        }
    }
}

// Excluir produto

if (isset($_POST['excluir'])) {

    $id = (int) $_POST['produto_id'];

    try {

        $consulta = $conexao->prepare(
            "DELETE FROM produtos WHERE id = ?"
        );

        $consulta->execute([$id]);

        $mensagem = "Produto excluído com sucesso!";

    } catch (PDOException $e) {

        $mensagem = "Não é possível excluir um produto
                     vinculado a um pedido.";
    }
}

// Buscar produto para edição

if (isset($_GET['editar'])) {

    $id = (int) $_GET['editar'];

    $consulta = $conexao->prepare(
        "SELECT * FROM produtos WHERE id = ?"
    );

    $consulta->execute([$id]);

    $editar = $consulta->fetch(PDO::FETCH_ASSOC);
}

// Buscar categorias

$categorias = $conexao->query(
    "SELECT * FROM categorias ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

// Buscar produtos

$sql = "SELECT produtos.*, categorias.nome AS categoria
        FROM produtos
        INNER JOIN categorias
        ON produtos.categoria_id = categorias.id
        ORDER BY produtos.id";

$produtos = $conexao->query($sql)
                    ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Produtos | Serenne Admin</title>

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
        Gerenciar produtos
    </h1>

    <?php if ($mensagem !== ""): ?>

        <div class="alert alerta-serenne">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <!-- Formulário -->

    <div class="caixa-admin mb-5">

        <h3 class="mb-4">

            <?= $editar ? 'Editar produto' : 'Cadastrar produto' ?>

        </h3>

        <form method="POST" action="produtos.php">

            <input type="hidden"
                   name="token"
                   value="<?= $token ?>">

            <input type="hidden"
                   name="id"
                   value="<?= $editar['id'] ?? 0 ?>">

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label">
                        Nome do produto
                    </label>

                    <input type="text"
                           name="nome"
                           class="form-control"
                           value="<?= htmlspecialchars($editar['nome'] ?? '') ?>"
                           required>

                </div>

                <div class="col-md-6">

                    <label class="form-label">
                        Categoria
                    </label>

                    <select name="categoria_id"
                            class="form-select"
                            required>

                        <option value="">
                            Selecione
                        </option>

                        <?php foreach ($categorias as $cat): ?>

                            <option value="<?= $cat['id'] ?>"

                                <?= ($editar['categoria_id'] ?? 0) == $cat['id']
                                    ? 'selected' : '' ?>>

                                <?= htmlspecialchars($cat['nome']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-12">

                    <label class="form-label">
                        Descrição
                    </label>

                    <textarea name="descricao"
                              class="form-control"
                              rows="3"
                              required><?= htmlspecialchars($editar['descricao'] ?? '') ?></textarea>

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Preço
                    </label>

                    <input type="number"
                           name="preco"
                           class="form-control"
                           step="0.01"
                           min="0.01"
                           value="<?= $editar['preco'] ?? '' ?>"
                           required>

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Estoque
                    </label>

                    <input type="number"
                           name="estoque"
                           class="form-control"
                           min="0"
                           value="<?= $editar['estoque'] ?? '' ?>"
                           required>

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Nome da imagem
                    </label>

                    <input type="text"
                           name="imagem"
                           class="form-control"
                           placeholder="exemplo.jpg"
                           value="<?= htmlspecialchars($editar['imagem'] ?? '') ?>"
                           required>

                </div>

            </div>

            <div class="mt-4">

                <button type="submit"
                        name="salvar"
                        class="btn btn-serenne">

                    <?= $editar ? 'Salvar alterações' : 'Cadastrar produto' ?>

                </button>

                <?php if ($editar): ?>

                    <a href="produtos.php"
                       class="btn btn-outline-serenne">

                        Cancelar

                    </a>

                <?php endif; ?>

            </div>

        </form>

    </div>

    <!-- Listagem dos produtos -->

    <div class="caixa-admin">

        <h3 class="mb-4">
            Produtos cadastrados
        </h3>

        <div class="table-responsive">

            <table class="table align-middle">

                <thead>

                    <tr>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($produtos as $produto): ?>

                        <tr>

                            <td>

                                <img src="../img/<?= htmlspecialchars($produto['imagem']) ?>"
                                     class="foto-admin"
                                     alt="<?= htmlspecialchars($produto['nome']) ?>">

                            </td>

                            <td>
                                <?= htmlspecialchars($produto['nome']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($produto['categoria']) ?>
                            </td>

                            <td>

                                R$ <?= number_format(
                                    $produto['preco'], 2, ',', '.'
                                ) ?>

                            </td>

                            <td>
                                <?= $produto['estoque'] ?>
                            </td>

                            <td>

                                <div class="d-flex gap-2">

                                    <a href="?editar=<?= $produto['id'] ?>"
                                       class="btn btn-outline-serenne btn-sm">

                                        Editar

                                    </a>

                                   <form method="POST" class="form-excluir">

                                        <input type="hidden"
                                               name="token"
                                               value="<?= $token ?>">

                                        <input type="hidden"
                                               name="produto_id"
                                               value="<?= $produto['id'] ?>">

                                        <button type="submit"
                                                name="excluir"
                                                class="btn btn-remover btn-sm">

                                            Excluir

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

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