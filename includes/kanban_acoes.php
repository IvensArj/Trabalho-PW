<?php
/**
 * ENDPOINT DE AÇÕES DO KANBAN
 * 
 * Processa requisições AJAX para:
 * - Atualizar o status de um projeto ou tarefa (arrastar entre colunas)
 * - Excluir um projeto ou tarefa
 * 
 * Retorna JSON com status da operação.
 */

header("Content-Type: application/json; charset=utf-8");

/**
 * Função auxiliar para padronizar as respostas JSON
 * @param mixed $dados  Dados a serem enviados
 * @param int   $codigo Código HTTP
 */
function responderJson($dados, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION["usuario_id"])) {
    responderJson(["ok" => false, "mensagem" => "Sessao expirada. Faca login novamente."], 401);
}

require_once __DIR__ . "/../config/conexao.php";
require_once __DIR__ . "/funcoes.php";

// Apenas requisições POST são aceitas
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    responderJson(["ok" => false, "mensagem" => "Metodo invalido."], 405);
}

// Proteção CSRF
validarCsrf();

$acao = $_POST["acao"] ?? "";
$tipo = $_POST["tipo"] ?? "";
$id = $_POST["id"] ?? null;
$status = $_POST["status"] ?? "";
$idUser = $_SESSION["usuario_id"];
$statusValidos = ["A Fazer", "Fazendo", "Feito"];

// Validação dos parâmetros obrigatórios
if (!$id || !in_array($tipo, ["projeto", "tarefa"])) {
    responderJson(["ok" => false, "mensagem" => "Dados invalidos."], 400);
}

try {

    /**
     * Ação: ATUALIZAR STATUS
     * Move o item para uma nova coluna do Kanban
     */
    if ($acao === "atualizar_status") {

        if (!in_array($status, $statusValidos)) {
            responderJson(["ok" => false, "mensagem" => "Status invalido."], 400);
        }

        // PROJETO: verifica propriedade e atualiza
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

        // TAREFA: valida posse via inner join com projetos
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

        // Atualiza automaticamente o status do projeto pai
        $statusProjeto = atualizarStatusProjeto($pdo, $tarefa->id_projeto, $idUser);

        responderJson([
            "ok" => true,
            "status" => $status,
            "project_status" => $statusProjeto
        ]);
    }

    /**
     * Ação: EXCLUIR
     * Remove o projeto ou tarefa definitivamente
     */
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

        // Tarefa: busca o id_projeto antes de excluir para atualizar status do projeto
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

    // Caso a ação informada não seja reconhecida
    responderJson(["ok" => false, "mensagem" => "Acao invalida."], 400);

} catch (PDOException $e) {
    error_log("Erro no kanban: " . $e->getMessage());
    responderJson(["ok" => false, "mensagem" => "Erro ao processar acao."], 500);
}