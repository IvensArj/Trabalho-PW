<?php $titulo = "Login"; require_once "../includes/header.php"; ?>

<div class="<?= ui_page("max-w-lg"); ?>">

    <h1 class="mb-6 text-3xl font-bold text-slate-950">Login</h1>

    <form action="autenticar.php" method="POST" class="<?= ui_card("p-5"); ?>">

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
                    id="senha"
                    class="<?= ui_input("rounded-r-none"); ?>"
                    placeholder="Senha"
                    autocomplete="new-password"
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

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="<?= ui_button("primary"); ?>">
                Entrar
            </button>

            <a href="cadastro.php" class="<?= ui_button("secondary"); ?>">
                Cadastrar
            </a>
        </div>

    </form>

</div>

<script>

    const senha = document.getElementById("senha");
    const toggle = document.getElementById("toggleSenha");

    toggle.addEventListener("click", function () {

        if (senha.type === "password") {

            senha.type = "text";
            toggle.innerHTML = '<i class="fi fi-sr-eye"></i>';

        } else {

            senha.type = "password";
            toggle.innerHTML = '<i class="fi fi-sr-eye-crossed"></i>';

        }

    });

</script>

<?php require_once "../includes/footer.php"; ?>
