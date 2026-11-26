<?php
session_start();

unset(
    $_SESSION['usuario_id'],
    $_SESSION['usuario_nome'],
    $_SESSION['usuario_tipo']
);

session_regenerate_id(true);

header("Location: index.php");
exit;