<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

$titulo = "Acesse sua conta";
require_once "../includes/header.php";
?>


<canvas id="mouse-trail"></canvas>

<main class=" login-page">
    <section class="login-brand-panel" aria-label="NEXO">
        <div class="login-brand-card">
            <div class="login-logo-frame">
                <img src="../assets/image/logotipo.png" alt="NEXO">
            </div>

            <h1 class="login-brand-title">Projetos, tarefas e ideias em um só flu<img src="../assets/image/logo.png" class="logo-text" alt="NEXO">o.</h1>

            <p class="login-brand-copy">
                Organize o trabalho com clareza, acompanhe o progresso e mantenha cada etapa conectada ao seu time.
            </p>
        </div>
    </section>

    <section class="login-auth-panel" aria-label="Acesso">
        <div class="login-auth-card">
            <div class="login-tabs" role="tablist" aria-label="Alternar formulario">
                <button
                    type="button"
                    class="active"
                    id="btnEntrar"
                    role="tab"
                    aria-selected="true"
                    aria-controls="formEntrar"
                    onclick="switchTab('entrar')"
                >
                    Entrar
                </button>
                <button
                    type="button"
                    class="inactive"
                    id="btnCriar"
                    role="tab"
                    aria-selected="false"
                    aria-controls="formCriar"
                    onclick="switchTab('criar')"
                >
                    Criar
                </button>
            </div>

            <div class="login-form-shell">
                <div class="login-rings" aria-hidden="true">
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <span class="login-ring"></span>
                    <?php endfor; ?>
                </div>

                <form
                    action="autenticar.php"
                    method="POST"
                    id="formEntrar"
                    class="login-form"
                    role="tabpanel"
                    aria-labelledby="btnEntrar"
                >
                    <div class="login-field">
                        <label for="emailEntrar" class="login-label">Email</label>
                        <input
                            type="email"
                            id="emailEntrar"
                            name="email"
                            class="login-input"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="senha" class="login-label">Senha</label>
                        <div class="login-password-wrap">
                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                class="login-input"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="login-eye" id="toggleSenha" aria-label="Mostrar ou ocultar senha">
                                <i class="fi fi-sr-eye-crossed"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-submit-row">
                        <button type="submit" class="btn btn-primary login-submit">Avancar</button>
                    </div>
                </form>

                <form
                    action="cadastrar.php"
                    method="POST"
                    id="formCriar"
                    class="login-form hidden"
                    role="tabpanel"
                    aria-labelledby="btnCriar"
                    hidden
                >
                    <div class="login-field">
                        <label for="nomeCriar" class="login-label">Nome</label>
                        <input
                            type="text"
                            id="nomeCriar"
                            name="nome"
                            class="login-input"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="emailCriar" class="login-label">Email</label>
                        <input
                            type="email"
                            id="emailCriar"
                            name="email"
                            class="login-input"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="login-field">
                        <label for="senhaCriar" class="login-label">Senha</label>
                        <div class="login-password-wrap">
                            <input
                                type="password"
                                id="senhaCriar"
                                name="senha"
                                class="login-input"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="login-eye" id="toggleSenhaCriar" aria-label="Mostrar ou ocultar senha">
                                <i class="fi fi-sr-eye-crossed"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-submit-row">
                        <button type="submit" class="btn btn-primary login-submit">Avancar</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>


<script>
  function limparFormulariosLogin() {
    document.querySelectorAll('#formEntrar, #formCriar').forEach(function (form) {
      form.reset();
    });

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
      input.type = 'password';
    });

    const toggleSenha = document.getElementById('toggleSenha');
    const toggleSenhaCriar = document.getElementById('toggleSenhaCriar');

    if (toggleSenha) {
      toggleSenha.innerHTML = '<i class="fi fi-sr-eye-crossed"></i>';
    }

    if (toggleSenhaCriar) {
      toggleSenhaCriar.innerHTML = '<i class="fi fi-sr-eye-crossed"></i>';
    }
  }

  window.addEventListener('pageshow', function (event) {
    const navegacao = performance.getEntriesByType('navigation')[0];
    const voltouPeloHistorico = event.persisted || (navegacao && navegacao.type === 'back_forward');

    if (voltouPeloHistorico) {
      limparFormulariosLogin();
    }
  });

  function switchTab(tab) {
    const formEntrar = document.getElementById('formEntrar');
    const formCriar  = document.getElementById('formCriar');
    const btnEntrar  = document.getElementById('btnEntrar');
    const btnCriar   = document.getElementById('btnCriar');

    if (tab === 'entrar') {
      formEntrar.classList.remove('hidden');
      formCriar.classList.add('hidden');
      formEntrar.hidden = false;
      formCriar.hidden = true;
      btnEntrar.classList.replace('inactive', 'active');
      btnCriar.classList.replace('active', 'inactive');
      btnEntrar.setAttribute('aria-selected', 'true');
      btnCriar.setAttribute('aria-selected', 'false');
    } else {
      formCriar.classList.remove('hidden');
      formEntrar.classList.add('hidden');
      formCriar.hidden = false;
      formEntrar.hidden = true;
      btnCriar.classList.replace('inactive', 'active');
      btnEntrar.classList.replace('active', 'inactive');
      btnCriar.setAttribute('aria-selected', 'true');
      btnEntrar.setAttribute('aria-selected', 'false');
    }
  }

  function configurarToggleSenha(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);

    if (!input || !button) {
      return;
    }

    button.addEventListener('click', function () {
      const deveMostrar = input.type === 'password';
      input.type = deveMostrar ? 'text' : 'password';
      button.innerHTML = deveMostrar
        ? '<i class="fi fi-sr-eye"></i>'
        : '<i class="fi fi-sr-eye-crossed"></i>';
    });
  }

  configurarToggleSenha('senha', 'toggleSenha');
  configurarToggleSenha('senhaCriar', 'toggleSenhaCriar');
</script>



<?php require_once "../includes/footer.php"; ?>
