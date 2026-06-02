<?php

header("Content-Type: application/json; charset=utf-8");

function responderJson($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

session_start();

if (!isset($_SESSION["usuario_id"])) {
    responderJson(["ok" => false, "mensagem" => "Sessao expirada. Faca login novamente."], 401);
}

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/funcoes.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderJson(["ok" => false, "mensagem" => "Metodo invalido."], 405);
}

validarCsrf();

$acao = $_POST["acao"] ?? "";
$idUser = $_SESSION["usuario_id"];

try {

    if ($acao === "criar") {
        $idTarefa = $_POST["id_tarefa"] ?? null;
        $titulo = trim($_POST["titulo"] ?? "");

        if (!$idTarefa || empty($titulo)) {
            responderJson(["ok" => false, "mensagem" => "Informe a subtarefa."], 400);
        }

        $sql = "SELECT tarefas.id_tarefa FROM tarefas
                INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
                WHERE tarefas.id_tarefa = ?
                AND projetos.id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idTarefa,
            $idUser
        ]);

        if (!$stmt->fetch(PDO::FETCH_OBJ)) {
            responderJson(["ok" => false, "mensagem" => "Tarefa nao encontrada."], 404);
        }

        $sql = "INSERT INTO subtarefas (id_tarefa, titulo)
                VALUES (?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idTarefa,
            $titulo
        ]);

        responderJson([
            "ok" => true,
            "subtarefa" => [
                "id_subtarefa" => $pdo->lastInsertId(),
                "titulo" => $titulo,
                "concluida" => 0
            ]
        ]);
    }

    if ($acao === "alternar") {
        $idSubtarefa = $_POST["id_subtarefa"] ?? null;
        $concluida = isset($_POST["concluida"]) && $_POST["concluida"] == "1" ? 1 : 0;

        if (!$idSubtarefa) {
            responderJson(["ok" => false, "mensagem" => "Subtarefa invalida."], 400);
        }

        $sql = "SELECT subtarefas.id_subtarefa FROM subtarefas
                INNER JOIN tarefas ON tarefas.id_tarefa = subtarefas.id_tarefa
                INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
                WHERE subtarefas.id_subtarefa = ?
                AND projetos.id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idSubtarefa,
            $idUser
        ]);

        if (!$stmt->fetch(PDO::FETCH_OBJ)) {
            responderJson(["ok" => false, "mensagem" => "Subtarefa nao encontrada."], 404);
        }

        $sql = "UPDATE subtarefas
                SET concluida = ?
                WHERE id_subtarefa = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $concluida,
            $idSubtarefa
        ]);

        responderJson([
            "ok" => true,
            "concluida" => $concluida
        ]);
    }

    if ($acao === "excluir") {
        $idSubtarefa = $_POST["id_subtarefa"] ?? null;

        if (!$idSubtarefa) {
            responderJson(["ok" => false, "mensagem" => "Subtarefa invalida."], 400);
        }

        $sql = "DELETE subtarefas FROM subtarefas
                INNER JOIN tarefas ON tarefas.id_tarefa = subtarefas.id_tarefa
                INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
                WHERE subtarefas.id_subtarefa = ?
                AND projetos.id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $idSubtarefa,
            $idUser
        ]);

        if ($stmt->rowCount() === 0) {
            responderJson(["ok" => false, "mensagem" => "Subtarefa nao encontrada."], 404);
        }

        responderJson(["ok" => true]);
    }

    responderJson(["ok" => false, "mensagem" => "Acao invalida."], 400);

} catch (PDOException $e) {
    error_log("Erro em subtarefas: " . $e->getMessage());
    responderJson(["ok" => false, "mensagem" => "Erro ao processar subtarefa."], 500);
}
