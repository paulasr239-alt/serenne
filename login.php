<?php
session_start();
require_once "config/conexao.php";

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $consulta = $conexao->prepare(
        "SELECT * FROM usuarios WHERE email = ?"
    );

    $consulta->execute([$email]);

    $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];

        header("Location: index.php");
        exit;

    } else {

        $mensagem = "E-mail ou senha incorretos.";

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Serenne</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

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

    <div class="caixa-formulario">

        <p class="categoria-produto text-center">
            JOALHERIA SERENNE
        </p>

        <h2 class="text-center mb-3">
            Bem-vindo à Serenne
        </h2>

        <p class="subtitulo-formulario">
            Entre para continuar suas compras.
        </p>

        <?php if (($_GET['cadastro'] ?? '') === 'ok'): ?>

            <div class="alert alert-success">
                Cadastro realizado com sucesso!
                Faça seu login.
            </div>

        <?php endif; ?>

        <?php if ($mensagem !== ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($mensagem) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    E-mail
                </label>

                <input type="email"
                       name="email"
                       class="form-control"
                       required>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Senha
                </label>

                <input type="password"
                       name="senha"
                       class="form-control"
                       required>

            </div>

            <button type="submit"
                    class="btn btn-serenne w-100">

                Entrar

            </button>

        </form>

        <p class="text-center mt-4 mb-0">

            Ainda não possui uma conta?

            <a href="cadastro.php" class="link-serenne">
                Cadastre-se
            </a>

        </p>

    </div>

</main>

<footer>

    <h4>SERENNE</h4>

    <p>Elegância em cada detalhe.</p>

    <p>© 2026 Serenne Joalheria.</p>

</footer>
<script src="script.js"></script>

</body>
</html>