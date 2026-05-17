<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $idUser = $_SESSION["usuario_id"];
    $dataEntrega = $_POST["data_entrega"] ?? null;
    $dataCriacao = date("Y-m-d H:i:s");

    if (empty($titulo) || empty($dataEntrega)) {
        die("Preencha todos os campos.");
    }

    $sql = "INSERT INTO projetos
            (titulo, descricao, id_user, data_entrega, data_criacao)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titulo,
        $descricao,
        $idUser,
        $dataEntrega,
        $dataCriacao
    ]);

    header("Location: ../dashboard/index.php");
    exit;
}

header("Location: cadastro.php");
exit;
