<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    validarCsrf();

    $projetoId = $_POST["id"] ?? null;
    $usuarioId = $_SESSION["usuario_id"];

    if (!$projetoId) {
        header("Location: ../dashboard/index.php");
        exit;
    }

    try {

        $sql = "DELETE FROM projetos
                WHERE id_projeto = ?
                AND id_user = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $projetoId,
            $usuarioId
        ]);

        redirecionarComFlash("../dashboard/index.php", "success", "Projeto excluído com sucesso!");
        exit;

    } catch (PDOException $e) {

        error_log("Erro ao excluir projeto: " . $e->getMessage());
        redirecionarComFlash("../dashboard/index.php", "error", "Erro ao excluir projeto.");

    }
}

header("Location: ../dashboard/index.php");
exit;
