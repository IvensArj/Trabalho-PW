<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    window.KANBAN_ENDPOINT = "../includes/kanban_acoes.php";
    window.SUBTASKS_ENDPOINT = "../includes/subtarefas_acoes.php";
    window.CSRF_TOKEN = "<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>";
</script>

<script src="https://unpkg.com/perfect-freehand"></script>
<script src="../assets/js/mouse-trail.js"></script>
<script src="../assets/js/kanban.js?v=<?= filemtime(__DIR__ . '/../assets/js/kanban.js'); ?>"></script>
<script src="../assets/js/subtarefas.js?v=<?= filemtime(__DIR__ . '/../assets/js/subtarefas.js'); ?>"></script>

<?php $flash = consumirFlashMessage(); ?>
<?php if (!empty($flash)): ?>
    <div id="app-popup" class="app-popup <?= htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8'); ?>" role="status" aria-live="polite" aria-atomic="true">
        <div class="app-popup-content">
            <strong><?= $flash['tipo'] === 'success' ? 'Sucesso' : 'Atenção'; ?></strong>
            <span><?= htmlspecialchars($flash['mensagem'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <button type="button" class="app-popup-close" aria-label="Fechar mensagem">×</button>
    </div>
    <script>
        (function () {
            const popup = document.getElementById('app-popup');
            if (!popup) return;

            const close = popup.querySelector('.app-popup-close');
            const dismiss = () => popup.classList.add('is-hiding');

            close?.addEventListener('click', dismiss);
            setTimeout(dismiss, 4500);
        })();
    </script>
<?php endif; ?>

<script>
    window.showAppMessage = function (message, type) {
        const popup = document.createElement('div');
        popup.className = 'app-popup ' + (type || 'error');
        popup.innerHTML = [
            '<div class="app-popup-content">',
            '<strong>' + (type === 'success' ? 'Sucesso' : 'Atenção') + '</strong>',
            '<span>' + message + '</span>',
            '</div>',
            '<button type="button" class="app-popup-close" aria-label="Fechar mensagem">×</button>'
        ].join('');

        document.body.appendChild(popup);
        const close = popup.querySelector('.app-popup-close');
        const dismiss = function () {
            popup.classList.add('is-hiding');
            setTimeout(function () { popup.remove(); }, 220);
        };

        close.addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    };
</script>

<?php if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario_id"])): ?>

    <script>
        window.addEventListener("pageshow", function (event) {
            const navegacao = performance.getEntriesByType("navigation")[0];
            const voltouPeloHistorico = event.persisted || (navegacao && navegacao.type === "back_forward");

            if (voltouPeloHistorico) {
                window.location.reload();
            }
        });
    </script>

<?php endif; ?>

<?php if (!empty($confirmarLogoutAoVoltar) && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario_id"])): ?>

    <script>
        (function () {
            const logoutUrl = "../crud_usuarios/logout.php";
            const stateKey = "auth-page";

            window.history.replaceState({ page: stateKey }, "", window.location.href);
            window.history.pushState({ page: stateKey }, "", window.location.href);

            window.addEventListener("popstate", function () {
                const confirmarLogout = window.confirm("Deseja sair da sua conta?");

                if (confirmarLogout) {
                    window.location.href = logoutUrl;
                    return;
                }

                window.history.pushState({ page: stateKey }, "", window.location.href);
            });
        })();
    </script>

<?php endif; ?>

</body>
</html>
