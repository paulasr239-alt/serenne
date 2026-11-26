<?php

$env = parse_ini_file(__DIR__ . '/../.env');

$host = $env['DB_HOST'];
$banco = $env['DB_NAME'];
$usuario = $env['DB_USER'];
$senha = $env['DB_PASS'];

try {
    $conexao = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $conexao->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados.");
}