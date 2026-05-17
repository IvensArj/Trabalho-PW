<?php

require_once "../includes/verificar_login.php";

?>

<?php $titulo = "Perfil"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <div class="<?= ui_card("p-5"); ?>">

        <h1 class="mb-6 text-3xl font-bold text-slate-950">Perfil</h1>

        <img
            src="<?= $_SESSION["usuario_foto"]; ?>"
            alt="Foto de perfil"
            class="mb-4 h-36 w-36 rounded-lg object-cover"
            width="150"
        >

        <div class="space-y-2 text-slate-700">
            <p>
                <strong class="font-semibold text-slate-950">Nome:</strong>
                <?= $_SESSION["usuario_nome"]; ?>
            </p>

            <p>
                <strong class="font-semibold text-slate-950">Email:</strong>
                <?= $_SESSION["usuario_email"]; ?>
            </p>
        </div>

        <div class="mt-6 flex flex-wrap gap-2">

            <a href="../dashboard/index.php" class="<?= ui_button("primary"); ?>">
                Dashboard
            </a>

            <a href="logout.php" class="<?= ui_button("outline-danger"); ?>">
                Sair
            </a>

            <form action="excluir.php" method="POST">

                <button
                    type="submit"
                    onclick="return confirm('Deseja mesmo excluir seu perfil?')"
                    class="<?= ui_button("danger"); ?>"
                >
                    Excluir cadastro
                </button>

            </form>

        </div>

    </div>

</div>

<?php require_once "../includes/footer.php"; ?>
