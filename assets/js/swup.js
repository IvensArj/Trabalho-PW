const swup = new Swup();

// Reexecuta JS quando troca de página
swup.hooks.on('page:view', () => {

    // aqui você reinicializa funções globais da página

    if (typeof switchTab === "function") {
        switchTab("entrar");
    }

    // exemplo: reativar efeitos
    if (typeof configurarToggleSenha === "function") {
        configurarToggleSenha('senha', 'toggleSenha');
        configurarToggleSenha('senhaCriar', 'toggleSenhaCriar');
    }
});