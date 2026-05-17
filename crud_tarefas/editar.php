<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";

$idTarefa = $_GET["id"] ?? null;
$idUser = $_SESSION["usuario_id"];

if (!$idTarefa) {
    header("Location: ../dashboard/index.php");
    exit;
}

$sql = "SELECT tarefas.* FROM tarefas
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

?>

<?php $titulo = "Editar Tarefa"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-6 text-3xl font-bold text-slate-950">Editar Tarefa</h1>

    <form action="atualizar.php" method="POST" class="<?= ui_card("p-5"); ?>">

        <input type="hidden" name="id" value="<?= $tarefa->id_tarefa; ?>">
        <input type="hidden" name="id_projeto" value="<?= $tarefa->id_projeto; ?>">

        <div class="mb-4">
            <input
                type="text"
                name="titulo"
                class="<?= ui_input(); ?>"
                value="<?= htmlspecialchars($tarefa->titulo); ?>"
                required
            >
        </div>

        <div class="mb-4">
            <textarea
                name="descricao"
                class="<?= ui_input("min-h-28"); ?>"
                rows="4"
            ><?= htmlspecialchars($tarefa->descricao); ?></textarea>
        </div>

        <div class="mb-4">
            <select name="prioridade" class="<?= ui_input(); ?>" required>
                <option value="Baixa" <?= $tarefa->prioridade == "Baixa" ? "selected" : ""; ?>>Baixa</option>
                <option value="Media" <?= $tarefa->prioridade == "Media" ? "selected" : ""; ?>>Media</option>
                <option value="Alta" <?= $tarefa->prioridade == "Alta" ? "selected" : ""; ?>>Alta</option>
            </select>
        </div>

        <div class="mb-4">
            <select name="status" class="<?= ui_input(); ?>" required>
                <option value="A Fazer" <?= $tarefa->status == "A Fazer" ? "selected" : ""; ?>>A Fazer</option>
                <option value="Fazendo" <?= $tarefa->status == "Fazendo" ? "selected" : ""; ?>>Fazendo</option>
                <option value="Feito" <?= $tarefa->status == "Feito" ? "selected" : ""; ?>>Feito</option>
            </select>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("warning"); ?>">
                Atualizar
            </button>

            <a href="../crud_projetos/projeto.php?id=<?= $tarefa->id_projeto; ?>" class="<?= ui_button("secondary"); ?>">
                Voltar
            </a>
        </div>

    </form>

</div>

<?php require_once "../includes/footer.php"; ?>
