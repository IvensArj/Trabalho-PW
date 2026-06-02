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
$stmt->execute([$idProjeto, $idUser]);
$projeto = $stmt->fetch(PDO::FETCH_OBJ);

if (!$projeto) {
    redirecionarComFlash("../dashboard/index.php", "error", "Projeto nao encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    validarCsrf();

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $prioridade = trim($_POST["prioridade"] ?? "Media");
    $status = trim($_POST["status"] ?? "A Fazer");

    if (empty($titulo)) {
        redirecionarComFlash("adicionar.php?id_projeto=" . urlencode((string) $idProjeto), "error", "Informe o titulo da tarefa.");
    }

    if (!in_array($prioridade, ["Baixa", "Media", "Alta"]) || !in_array($status, ["A Fazer", "Fazendo", "Feito"])) {
        redirecionarComFlash("adicionar.php?id_projeto=" . urlencode((string) $idProjeto), "error", "Dados invalidos.");
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

$titulo = "Adicionar Tarefa";
require_once "../includes/header.php";
?>

<main>
    <div class="form-notebook">
        <div class="spiral-bar" aria-hidden="true">
            <?php for ($i = 0; $i < 12; $i++): ?>
                <span class="spiral-ring"></span>
            <?php endfor; ?>
        </div>

        <div class="form-inner">
            <header class="form-header">
                <h1 class="form-title">Adicionar Tarefa</h1>
                <p class="form-subtitle">Projeto: <?= htmlspecialchars($projeto->titulo); ?></p>
            </header>

            <form action="adicionar.php" method="POST">
                <?= csrfInput(); ?>
                <input type="hidden" name="id_projeto" value="<?= htmlspecialchars($idProjeto); ?>">

                <div class="form-group">
                    <label for="titulo" class="form-label">Titulo da tarefa</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        class="form-control"
                        placeholder="Ex: Definir escopo inicial"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="descricao" class="form-label">Descricao</label>
                    <textarea
                        id="descricao"
                        name="descricao"
                        class="form-control"
                        placeholder="Detalhes da tarefa..."
                        rows="4"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="prioridade" class="form-label">Prioridade</label>
                    <select id="prioridade" name="prioridade" class="form-control" required>
                        <option value="Baixa">Baixa</option>
                        <option value="Media" selected>Media</option>
                        <option value="Alta">Alta</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="A Fazer" selected>A Fazer</option>
                        <option value="Fazendo">Fazendo</option>
                        <option value="Feito">Feito</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Salvar tarefa
                    </button>

                    <a href="../crud_projetos/projeto.php?id=<?= htmlspecialchars($idProjeto); ?>" class="btn btn-secondary">
                        Cancelar e voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once "../includes/footer.php"; ?>
