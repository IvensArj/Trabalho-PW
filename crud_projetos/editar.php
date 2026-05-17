<?php

require_once "../includes/verificar_login.php";
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

?>

<?php $titulo = "Editar Projeto"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-6 text-3xl font-bold text-slate-950">
        Editar Projeto
    </h1>

    <form action="atualizar.php" method="POST" class="<?= ui_card("p-5"); ?>">

        <input type="hidden" name="id" value="<?= $projeto->id_projeto; ?>">

        <div class="mb-4">
            <input
                type="text"
                name="titulo"
                class="<?= ui_input(); ?>"
                value="<?= htmlspecialchars($projeto->titulo); ?>"
                required
            >
        </div>

        <div class="mb-4">
            <textarea
                name="descricao"
                class="<?= ui_input("min-h-28"); ?>"
                rows="4"
            ><?= htmlspecialchars($projeto->descricao); ?></textarea>
        </div>

        <div class="mb-4">
            <select name="status" class="<?= ui_input(); ?>" required>
                <option value="A Fazer" <?= $projeto->status == "A Fazer" ? "selected" : ""; ?>>A Fazer</option>
                <option value="Fazendo" <?= $projeto->status == "Fazendo" ? "selected" : ""; ?>>Fazendo</option>
                <option value="Feito" <?= $projeto->status == "Feito" ? "selected" : ""; ?>>Feito</option>
            </select>
        </div>

        <div class="mb-4">
            <label for="data_entrega" class="block text-sm font-medium text-slate-700 mb-1">
                Data de Entrega
            </label>
            <input
                type="date"
                id="data_entrega"
                name="data_entrega"
                class="<?= ui_input(); ?>"
                value="<?= htmlspecialchars($projeto->data_entrega); ?>"
                required
            >
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("warning"); ?>">
                Atualizar
            </button>

            <a href="../dashboard/index.php" class="<?= ui_button("secondary"); ?>">
                Voltar
            </a>
        </div>

    </form>

</div>

<?php require_once "../includes/footer.php"; ?>
