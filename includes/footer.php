<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    window.KANBAN_ENDPOINT = "../includes/kanban_acoes.php";
    window.SUBTASKS_ENDPOINT = "../includes/subtarefas_acoes.php";
</script>

<script src="https://unpkg.com/perfect-freehand"></script>
<script src="../assets/js/mouse-trail.js"></script>
<script src="../assets/js/kanban.js?v=<?= filemtime(__DIR__ . '/../assets/js/kanban.js'); ?>"></script>
<script src="../assets/js/subtarefas.js?v=<?= filemtime(__DIR__ . '/../assets/js/subtarefas.js'); ?>"></script>

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
