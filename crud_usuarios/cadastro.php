<?php $titulo = "Cadastro"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-6 text-3xl font-bold text-slate-950">Cadastro</h1>

    <form action="cadastrar.php" method="POST" class="<?= ui_card("p-5"); ?>">

        <div class="mb-4">
            <input
                type="text"
                name="nome"
                class="<?= ui_input(); ?>"
                placeholder="Nome"
                required
            >
        </div>

        <div class="mb-4">
            <input
                type="email"
                name="email"
                class="<?= ui_input(); ?>"
                placeholder="Email"
                required
            >
        </div>

        <div class="mb-4">
            <div class="flex">
                <input
                    type="password"
                    name="senha"
                    class="<?= ui_input("rounded-r-none"); ?>"
                    placeholder="Senha"
                    autocomplete="new-password"
                    id="senha"
                    required
                >
                <button
                    type="button"
                    class="<?= ui_button("outline-secondary", "md", "rounded-l-none border-l-0"); ?>"
                    id="toggleSenha"
                >
                    <i class="fi fi-sr-eye-crossed"></i>
                </button>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex">
                <input
                    type="password"
                    name="confirmar_senha"
                    id="confirmar_senha"
                    class="<?= ui_input("rounded-r-none"); ?>"
                    placeholder="Confirmar senha"
                    autocomplete="new-password"
                    required
                >
                <button
                    type="button"
                    class="<?= ui_button("outline-secondary", "md", "rounded-l-none border-l-0"); ?>"
                    id="toggleCSenha"
                >
                    <i class="fi fi-sr-eye-crossed"></i>
                </button>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("primary"); ?>">
                Cadastrar
            </button>

            <a href="login.php" class="<?= ui_button("secondary"); ?>">
                J&aacute; tenho conta
            </a>
        </div>

    </form>

</div>

<script>

    const senha = document.getElementById("senha");
    const confirmarSenha = document.getElementById("confirmar_senha");
    const toggleSenha = document.getElementById("toggleSenha");
    const toggleCSenha = document.getElementById("toggleCSenha");

    toggleSenha.addEventListener("click", function () {

        if (senha.type === "password") {

            senha.type = "text";
            toggleSenha.innerHTML = '<i class="fi fi-sr-eye"></i>';

        } else {

            senha.type = "password";
            toggleSenha.innerHTML = '<i class="fi fi-sr-eye-crossed"></i>';

        }

    });

    toggleCSenha.addEventListener("click", function () {

        if (confirmarSenha.type === "password") {

            confirmarSenha.type = "text";
            toggleCSenha.innerHTML = '<i class="fi fi-sr-eye"></i>';

        } else {

            confirmarSenha.type = "password";
            toggleCSenha.innerHTML = '<i class="fi fi-sr-eye-crossed"></i>';

        }

    });

</script>

<?php require_once "../includes/footer.php"; ?>
