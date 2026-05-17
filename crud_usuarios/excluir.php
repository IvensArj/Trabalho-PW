<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuarioId = $_SESSION["usuario_id"];

    try {

        $sql = "DELETE FROM usuarios WHERE id_user = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$usuarioId]);

        session_destroy();

        header("Location: login.php");

        exit;

    } catch (PDOException $e) {

        error_log("Erro ao excluir cadastro: " . $e->getMessage());
        die("Erro ao excluir cadastro.");

    }

}

header("Location: perfil.php");

exit;
