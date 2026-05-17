
<?php

$host = "localhost";
$banco = "pw-crud1";
$usuario = "root";
$senha = "";

try {
    $pdo = new PDO(
    "mysql:host=$host;dbname=$banco;charset=utf8mb4",
    $usuario,
    $senha
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

} catch (PDOException $e) {
    error_log("Erro na conexao com o banco de dados: " . $e->getMessage());
    die("Erro ao conectar ao banco de dados.");
}
