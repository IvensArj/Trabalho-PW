<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";
require_once "../includes/funcoes.php";

$idProjeto = $_GET["id_projeto"] ?? $_POST["id_projeto"] ?? null;
$idUser = $_SESSION["usuario_id"];

if (!$idProjeto) {
    header("Location: ../dashboard/index.php");
    exit;
}

$sql = "SELECT id_projeto, titulo FROM projetos
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $prioridade = trim($_POST["prioridade"] ?? "Media");
    $status = trim($_POST["status"] ?? "A Fazer");

    if (empty($titulo)) {
        die("Informe o titulo da tarefa.");
    }

    if (!in_array($prioridade, ["Baixa", "Media", "Alta"]) || !in_array($status, ["A Fazer", "Fazendo", "Feito"])) {
        die("Dados invalidos.");
    }

    $sql = "INSERT INTO tarefas
            (titulo, descricao, status, prioridade, id_projeto)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $titulo,
        $descricao,
        $status,
        $prioridade,
        $idProjeto
    ]);

    atualizarStatusProjeto($pdo, $idProjeto, $idUser);

    header("Location: ../crud_projetos/projeto.php?id=" . $idProjeto);
    exit;
}

?>

<?php $titulo = "Adicionar Tarefa"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-2 text-3xl font-bold text-slate-950">Adicionar Tarefa</h1>

    <p class="mb-6 text-slate-600">
        Projeto: <?= htmlspecialchars($projeto->titulo); ?>
    </p>

    <form action="adicionar.php" method="POST" class="<?= ui_card("p-5"); ?>">

        <input type="hidden" name="id_projeto" value="<?= htmlspecialchars($idProjeto); ?>">

        <div class="mb-4">
            <input
                type="text"
                name="titulo"
                class="<?= ui_input(); ?>"
                placeholder="Titulo da tarefa"
                required
            >
        </div>

        <div class="mb-4">
            <textarea
                name="descricao"
                class="<?= ui_input("min-h-28"); ?>"
                placeholder="Descricao da tarefa"
                rows="4"
            ></textarea>
        </div>

        <div class="mb-4">
            <select name="prioridade" class="<?= ui_input(); ?>" required>
                <option value="Baixa">Baixa</option>
                <option value="Media" selected>Media</option>
                <option value="Alta">Alta</option>
            </select>
        </div>

        <div class="mb-4">
            <select name="status" class="<?= ui_input(); ?>" required>
                <option value="A Fazer" selected>A Fazer</option>
                <option value="Fazendo">Fazendo</option>
                <option value="Feito">Feito</option>
            </select>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("primary"); ?>">
                Salvar
            </button>

            <a href="../crud_projetos/projeto.php?id=<?= htmlspecialchars($idProjeto); ?>" class="<?= ui_button("secondary"); ?>">
                Voltar
            </a>
        </div>

    </form>

</div>

<?php require_once "../includes/footer.php"; ?>
