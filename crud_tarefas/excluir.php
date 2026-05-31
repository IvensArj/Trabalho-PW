<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";
require_once "../includes/funcoes.php";

$idTarefa = $_POST["id"] ?? null;
$idUser = $_SESSION["usuario_id"];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../dashboard/index.php");
    exit;
}

if (!$idTarefa) {
    header("Location: ../dashboard/index.php");
    exit;
}

try {

    $sql = "SELECT tarefas.id_projeto FROM tarefas
            INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
            WHERE tarefas.id_tarefa = ?
            AND projetos.id_user = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $idTarefa,
        $idUser
    ]);

    $tarefa = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$tarefa) {
        die("Tarefa nao encontrada.");
    }

    $sql = "DELETE tarefas FROM tarefas
            INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
            WHERE tarefas.id_tarefa = ?
            AND projetos.id_user = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $idTarefa,
        $idUser
    ]);

    atualizarStatusProjeto($pdo, $tarefa->id_projeto, $idUser);

    header("Location: ../crud_projetos/projeto.php?id=" . $tarefa->id_projeto);
    exit;

} catch (PDOException $e) {

    error_log("Erro ao excluir tarefa: " . $e->getMessage());
    die("Erro ao excluir tarefa.");

}
