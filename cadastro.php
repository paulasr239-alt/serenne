<?php
session_start();
require_once "config/conexao.php";

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($nome) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $mensagem = "Preencha os dados corretamente.";

    } elseif (strlen($senha) < 6) {

        $mensagem = "A senha deve ter pelo menos 6 caracteres.";

    } else {

        $consulta = $conexao->prepare(
            "SELECT id FROM usuarios WHERE email = ?"
        );

        $consulta->execute([$email]);

        if ($consulta->fetch()) {

            $mensagem = "Este e-mail já está cadastrado.";

        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuarios
                    (nome, email, senha, tipo)
                    VALUES (?, ?, ?, 'cliente')";

            $consulta = $conexao->prepare($sql);

            $consulta->execute([
                $nome,
                $email,
                $senhaHash
            ]);

            header("Location: login.php?cadastro=ok");
            exit;
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

    <title>Cadastro | Serenne</title>

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
            Crie sua conta
        </h2>

        <p class="subtitulo-formulario">
            Faça parte do universo Serenne.
        </p>

        <?php if ($mensagem !== ""): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($mensagem) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Nome completo
                </label>

                <input type="text"
                       name="nome"
                       class="form-control"
                       required>

            </div>

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
                       minlength="6"
                       required>

            </div>

            <button type="submit"
                    class="btn btn-serenne w-100">

                Criar conta

            </button>

        </form>

        <p class="text-center mt-4 mb-0">

            Já possui uma conta?

            <a href="login.php" class="link-serenne">
                Entrar
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