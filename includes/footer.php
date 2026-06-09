<?php
/**
 * FOOTER GLOBAL DO SISTEMA
 * 
 * Este arquivo é incluído no final das páginas e contém:
 * - Carregamento de bibliotecas JavaScript (SortableJS, Perfect Freehand)
 * - Definição de constantes globais (endpoints da API, token CSRF)
 * - Inclusão dos scripts do Kanban e Subtarefas (com cache busting)
 * - Exibição de mensagens flash (pop-ups do sistema)
 * - Função global `showAppMessage` para criação dinâmica de pop-ups
 * - Mecanismos de segurança ao navegar pelo histórico do navegador
 * - Confirmação de logout ao pressionar "voltar" (quando configurado)
 */

// Biblioteca para ordenação drag-and-drop (Kanban)
?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

<script>
    /**
     * Constantes globais para comunicação AJAX 
     * 
     * KANBAN_ENDPOINT: endpoint que processa ações de atualização de status e exclusão
     * SUBTASKS_ENDPOINT: endpoint para criar/alternar/excluir subtarefas
     * CSRF_TOKEN: token de segurança contra ataques CSRF, gerado pelo PHP
     */
    window.KANBAN_ENDPOINT = "../includes/kanban_acoes.php";
    window.SUBTASKS_ENDPOINT = "../includes/subtarefas_acoes.php";
    window.CSRF_TOKEN = "<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>";
</script>

<!-- Biblioteca para suavização do tracejado com lápis -->
<script src="https://unpkg.com/perfect-freehand"></script>
<!-- Rastro do mouse customizado -->
<script src="../assets/js/mouse-trail.js"></script>

<!-- Scripts principais do Kanban e subtarefas com cache busting baseado no timestamp do arquivo -->
<script src="../assets/js/kanban.js?v=<?= filemtime(__DIR__ . '/../assets/js/kanban.js'); ?>"></script>
<script src="../assets/js/subtarefas.js?v=<?= filemtime(__DIR__ . '/../assets/js/subtarefas.js'); ?>"></script>

<?php
// Recupera mensagem flash da sessão (ex: após redirecionamento)
$flash = consumirFlashMessage();
?>
<?php if (!empty($flash)): ?>
    <!-- Pop-up de mensagem (sucesso ou erro) renderizado via PHP -->
    <div id="app-popup" class="app-popup <?= htmlspecialchars($flash['tipo'], ENT_QUOTES, 'UTF-8'); ?>" role="status" aria-live="polite" aria-atomic="true">
        <div class="app-popup-content">
            <strong><?= $flash['tipo'] === 'success' ? 'Sucesso' : 'Atenção'; ?></strong>
            <span><?= htmlspecialchars($flash['mensagem'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
        <button type="button" class="app-popup-close" aria-label="Fechar mensagem">×</button>
    </div>
    <script>
        /**
         * Inicializa o comportamento do pop-up renderizado:
         * - Botão de fechar
         * - Auto-dismiss após 4.5 segundos
         * - Classe 'is-hiding' dispara transição CSS
         */
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
    /**
     * Função global para exibir mensagens de feedback ao usuário
     * Cria dinamicamente um pop-up com a mesma estrutura visual
     * 
     * @param {string} message - Texto da mensagem
     * @param {string} type    - Tipo: 'success' ou 'error'
     */
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
            // Remove o elemento após a transição (220ms)
            setTimeout(function () { popup.remove(); }, 220);
        };

        close.addEventListener('click', dismiss);
        setTimeout(dismiss, 4500);
    };
</script>

<?php
/**
 * Se o usuário estiver logado, adiciona um listener que força o reload
 * quando a página é restaurada do cache do navegador (back/forward).
 * Isso evita inconsistências de estado após logout em outra aba.
 */
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario_id"])): ?>
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

<?php
/**
 * Lógica de confirmação de logout ao pressionar o botão "voltar" do navegador.
 * 
 * Quando $confirmarLogoutAoVoltar está definida (ex: na página do usuário),
 * manipula o histórico para interceptar o popstate e perguntar se deseja sair.
 * Isso impede que o usuário volte a páginas restritas após logout.
 */
if (!empty($confirmarLogoutAoVoltar) && session_status() === PHP_SESSION_ACTIVE && isset($_SESSION["usuario_id"])): ?>
    <script>
        (function () {
            const logoutUrl = "../crud_usuarios/logout.php";
            const stateKey = "auth-page";

            // Substitui o estado atual e adiciona um novo para podermos detectar o "voltar"
            window.history.replaceState({ page: stateKey }, "", window.location.href);
            window.history.pushState({ page: stateKey }, "", window.location.href);

            window.addEventListener("popstate", function () {
                const confirmarLogout = window.confirm("Deseja sair da sua conta?");

                if (confirmarLogout) {
                    window.location.href = logoutUrl;
                    return;
                }

                // Se cancelou, reempilha o estado para manter o usuário na página
                window.history.pushState({ page: stateKey }, "", window.location.href);
            });
        })();
    </script>
<?php endif; ?>

</body>
</html>