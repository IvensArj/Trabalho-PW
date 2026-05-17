<?php

require_once "../includes/verificar_login.php";
require_once "../config/conexao.php";
require_once "../includes/funcoes.php";

$id_user = $_SESSION["usuario_id"];

$sql = "SELECT * FROM projetos
        WHERE id_user = ?
        ORDER BY id_projeto DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([$id_user]);

$projetos = $stmt->fetchAll(PDO::FETCH_OBJ);

?>

<?php $titulo = "Dashboard"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page(); ?>">

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="mb-1 text-3xl font-bold tracking-normal text-slate-950">
                Bem-vindo,
                <?= htmlspecialchars($_SESSION["usuario_nome"]); ?>
            </h1>

            <p class="text-slate-600">
                Gerencie seus projetos
            </p>

        </div>

        <a
            href="../crud_projetos/cadastro.php"
            class="<?= ui_button("success"); ?>"
        >
            Criar Projeto
        </a>

    </div>


    <?php render($projetos); ?>


    <div class="mt-8 flex flex-wrap gap-2">

        <a
            href="../crud_usuarios/perfil.php"
            class="<?= ui_button("primary"); ?>"
        >
            Perfil
        </a>

        <a
            href="../crud_usuarios/logout.php"
            class="<?= ui_button("danger"); ?>"
        >
            Sair
        </a>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>
