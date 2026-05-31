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

$acao = $_POST["acao"] ?? "";
$tipo = $_POST["tipo"] ?? "";
$id = $_POST["id"] ?? null;
$status = $_POST["status"] ?? "";
$idUser = $_SESSION["usuario_id"];
$statusValidos = ["A Fazer", "Fazendo", "Feito"];

if (!$id || !in_array($tipo, ["projeto", "tarefa"])) {
    responderJson(["ok" => false, "mensagem" => "Dados invalidos."], 400);
}

try {

    if ($acao === "atualizar_status") {

        if (!in_array($status, $statusValidos)) {
            responderJson(["ok" => false, "mensagem" => "Status invalido."], 400);
        }

        if ($tipo === "projeto") {
            $sql = "SELECT id_projeto FROM projetos
                    WHERE id_projeto = ?
                    AND id_user = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id,
                $idUser
            ]);

            if (!$stmt->fetch(PDO::FETCH_OBJ)) {
                responderJson(["ok" => false, "mensagem" => "Projeto nao encontrado."], 404);
            }

            $sql = "UPDATE projetos
                    SET status = ?
                    WHERE id_projeto = ?
                    AND id_user = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $status,
                $id,
                $idUser
            ]);

            responderJson(["ok" => true, "status" => $status]);
        }

        $sql = "SELECT tarefas.id_projeto FROM tarefas
                INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
                WHERE tarefas.id_tarefa = ?
                AND projetos.id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id,
            $idUser
        ]);

        $tarefa = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$tarefa) {
            responderJson(["ok" => false, "mensagem" => "Tarefa nao encontrada."], 404);
        }

        $sql = "UPDATE tarefas
                SET status = ?
                WHERE id_tarefa = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status,
            $id
        ]);

        $statusProjeto = atualizarStatusProjeto($pdo, $tarefa->id_projeto, $idUser);

        responderJson([
            "ok" => true,
            "status" => $status,
            "project_status" => $statusProjeto
        ]);
    }

    if ($acao === "excluir") {

        if ($tipo === "projeto") {
            $sql = "DELETE FROM projetos
                    WHERE id_projeto = ?
                    AND id_user = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id,
                $idUser
            ]);

            if ($stmt->rowCount() === 0) {
                responderJson(["ok" => false, "mensagem" => "Projeto nao encontrado."], 404);
            }

            responderJson(["ok" => true]);
        }

        $sql = "SELECT tarefas.id_projeto FROM tarefas
                INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
                WHERE tarefas.id_tarefa = ?
                AND projetos.id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id,
            $idUser
        ]);

        $tarefa = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$tarefa) {
            responderJson(["ok" => false, "mensagem" => "Tarefa nao encontrada."], 404);
        }

        $sql = "DELETE FROM tarefas
                WHERE id_tarefa = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $statusProjeto = atualizarStatusProjeto($pdo, $tarefa->id_projeto, $idUser);

        responderJson([
            "ok" => true,
            "project_status" => $statusProjeto
        ]);
    }

    responderJson(["ok" => false, "mensagem" => "Acao invalida."], 400);

} catch (PDOException $e) {
    error_log("Erro no kanban: " . $e->getMessage());
    responderJson(["ok" => false, "mensagem" => "Erro ao processar acao."], 500);
}
