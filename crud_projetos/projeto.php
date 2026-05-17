<?php

require_once "../includes/verificar_login.php";
require_once "../includes/funcoes.php";
require_once "../config/conexao.php";

$idProjeto = $_GET["id"] ?? null;
$idUser = $_SESSION["usuario_id"];

if (!$idProjeto) {
    header("Location: ../dashboard/index.php");
    exit;
}

$sql = "SELECT * FROM projetos
        WHERE id_projeto = ?
        AND id_user = ?";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $idProjeto,
    $idUser
]);

$projeto = $stmt->fetch(PDO::FETCH_OBJ);

if (!$projeto) {
    die("Projeto nao encontrado.");
}

$sql = "SELECT tarefas.* FROM tarefas
        INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
        WHERE tarefas.id_projeto = ?
        AND projetos.id_user = ?
        ORDER BY tarefas.id_tarefa DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    $idProjeto,
    $idUser
]);

$tarefas = $stmt->fetchAll(PDO::FETCH_OBJ);

if (count($tarefas) > 0) {
    $tarefasPorId = [];

    foreach ($tarefas as $tarefa) {
        $tarefa->subtarefas = [];
        $tarefasPorId[$tarefa->id_tarefa] = $tarefa;
    }

    $idsTarefas = array_keys($tarefasPorId);
    $placeholders = implode(",", array_fill(0, count($idsTarefas), "?"));

    $sql = "SELECT subtarefas.* FROM subtarefas
            INNER JOIN tarefas ON tarefas.id_tarefa = subtarefas.id_tarefa
            INNER JOIN projetos ON projetos.id_projeto = tarefas.id_projeto
            WHERE subtarefas.id_tarefa IN ($placeholders)
            AND projetos.id_user = ?
            ORDER BY subtarefas.id_subtarefa ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($idsTarefas, [$idUser]));

    foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $subtarefa) {
        $tarefasPorId[$subtarefa->id_tarefa]->subtarefas[] = $subtarefa;
    }
}

?>

<?php $titulo = "Projeto"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-6xl"); ?>">

    <div class="<?= ui_card(); ?>">

        <div class="<?= ui_card_body(); ?>">

            <div class="mb-6">

                <h1 class="mb-2 text-3xl font-bold text-slate-950">
                    <?= htmlspecialchars($projeto->titulo); ?>
                </h1>

                <div class="flex flex-wrap gap-3 text-sm text-slate-500">
                    <span>Status: <span data-project-status-value><?= htmlspecialchars($projeto->status); ?></span></span>
                    <span>Entrega: <?= htmlspecialchars($projeto->data_entrega); ?></span>
                </div>

            </div>

            <div class="mb-8">

                <h2 class="mb-2 text-lg font-semibold text-slate-900">
                    Descricao
                </h2>

                <p class="leading-relaxed text-slate-700">
                    <?= nl2br(htmlspecialchars($projeto->descricao)); ?>
                </p>

            </div>

            <div class="mb-6">
                <?php render($tarefas, "tarefa"); ?>
            </div>

            <div class="flex flex-wrap gap-2">

                <a
                    href="../crud_tarefas/adicionar.php?id_projeto=<?= $projeto->id_projeto; ?>"
                    class="<?= ui_button("primary"); ?>"
                >
                    Adicionar Tarefa
                </a>

                <a
                    href="../crud_projetos/editar.php?id=<?= $projeto->id_projeto; ?>"
                    class="<?= ui_button("warning"); ?>"
                >
                    Editar
                </a>

                <a
                    href="../dashboard/index.php"
                    class="<?= ui_button("secondary"); ?>"
                >
                    Voltar
                </a>

            </div>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>
