<?php

$host = getenv("DB_HOST") ?: "db";
$banco = getenv("DB_NAME") ?: "crud_db";
$usuario = getenv("DB_USER") ?: "user";
$senha = getenv("DB_PASS") ?: "password";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

} catch (PDOException $e) {

    error_log("Erro ao conectar no banco: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados.");

}
