<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $idProjeto = $_POST["id"] ?? null;
    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $status = trim($_POST["status"] ?? "");
    $dataEntrega = $_POST["data_entrega"] ?? null;
    $idUser = $_SESSION["usuario_id"];

    if (!$idProjeto || empty($titulo) || empty($dataEntrega) || !in_array($status, ["A Fazer", "Fazendo", "Feito"])) {
        die("Dados invalidos.");
    }

    try {

        $sql = "UPDATE projetos
                SET titulo = ?,
                    descricao = ?,
                    status = ?,
                    data_entrega = ?
                WHERE id_projeto = ?
                AND id_user = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $titulo,
            $descricao,
            $status,
            $dataEntrega,
            $idProjeto,
            $idUser
        ]);

        header("Location: ../dashboard/index.php");
        exit;

    } catch (PDOException $e) {

        error_log("Erro ao atualizar projeto: " . $e->getMessage());
        die("Erro ao atualizar projeto.");

    }
}

header("Location: ../dashboard/index.php");
exit;
