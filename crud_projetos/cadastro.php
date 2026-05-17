<?php require_once "../includes/verificar_login.php"; ?>

<?php $titulo = "Criar Projeto"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-6 text-3xl font-bold text-slate-950">Criar Projeto</h1>

    <form action="cadastrar.php" method="POST" class="<?= ui_card("p-5"); ?>">

        <div class="mb-4">
            <input
                type="text"
                name="titulo"
                class="<?= ui_input(); ?>"
                placeholder="Titulo do projeto"
                required
            >
        </div>

        <div class="mb-4">
            <textarea
                name="descricao"
                class="<?= ui_input("min-h-28"); ?>"
                placeholder="Descricao do projeto"
                rows="4"
            ></textarea>
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
                required
            >
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("primary"); ?>">
                Criar
            </button>

            <a href="../dashboard/index.php" class="<?= ui_button("secondary"); ?>">
                Voltar
            </a>
        </div>

    </form>

</div>

<?php require_once "../includes/footer.php"; ?>
