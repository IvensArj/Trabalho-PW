<?php
/**
 * FUNÇÕES UTILITÁRIAS DO SISTEMA
 *
 * Contém funções reutilizáveis para:
 * - Gerenciamento de sessão segura (início e CSRF)
 * - Mensagens flash (feedback após redirecionamento)
 * - Upload e manipulação de avatares
 * - Análise de prazos de entrega (projetos)
 * - Atualização automática de status de projetos baseado nas tarefas
 * - Limitação de texto para exibição no Kanban
 * - Renderização do quadro Kanban (projetos e tarefas)
 */

/**
 * Inicia a sessão apenas se ainda não estiver ativa
 */
function iniciarSessaoSegura(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Gera ou recupera o token CSRF da sessão
 * @return string Token CSRF
 */
function csrfToken(): string
{
    iniciarSessaoSegura();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Cria um campo hidden com o token CSRF para inclusão em formulários
 * @return string HTML do input hidden
 */
function csrfInput(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Valida o token CSRF recebido via POST contra o da sessão
 * @throws RuntimeException se o token for inválido
 */
function validarCsrf(): void
{
    iniciarSessaoSegura();

    $tokenPost = $_POST['csrf_token'] ?? '';
    $tokenSessao = $_SESSION['csrf_token'] ?? '';

    if (!$tokenPost || !$tokenSessao || !hash_equals($tokenSessao, $tokenPost)) {
        http_response_code(403);
        throw new RuntimeException('Requisição inválida.');
    }
}

/**
 * Armazena uma mensagem flash na sessão para exibição na próxima requisição
 *
 * @param string $tipo    success|error|info...
 * @param string $mensagem Texto da mensagem
 */
function flashMessage(string $tipo, string $mensagem): void
{
    iniciarSessaoSegura();
    $_SESSION['flash_message'] = [
        'tipo' => $tipo,
        'mensagem' => $mensagem,
    ];
}

/**
 * Recupera e remove a mensagem flash da sessão
 * @return array|null Array com 'tipo' e 'mensagem' ou null
 */
function consumirFlashMessage(): ?array
{
    iniciarSessaoSegura();

    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}

/**
 * Define uma mensagem flash e redireciona para a URL especificada
 * (atalho para flashMessage + header Location)
 */
function redirecionarComFlash(string $url, string $tipo, string $mensagem): void
{
    flashMessage($tipo, $mensagem);
    header("Location: " . $url);
    exit;
}

/**
 * Retorna a URL correta do avatar do usuário, tratando caminhos padrão
 *
 * @param string|null $fotoPerfil Nome do arquivo ou URL
 * @return string URL final da imagem
 */
function avatarUsuarioUrl(?string $fotoPerfil): string
{
    $fotoPerfil = trim((string) $fotoPerfil);

    // Se for vazio ou o padrão, retorna imagem default
    if ($fotoPerfil === '' || $fotoPerfil === 'default.png' || $fotoPerfil === '../assets/image/default.png') {
        return '../assets/image/default.png';
    }

    // Se for uma URL absoluta ou relativa à raiz, mantém como está
    if (preg_match('#^(?:https?:)?//#i', $fotoPerfil) || str_starts_with($fotoPerfil, '/')) {
        return $fotoPerfil;
    }

    // Senão, assume que está na pasta de uploads de avatares
    return '../uploads/avatares/' . ltrim($fotoPerfil, '/\\');
}

/**
 * Salva uma imagem em base64 (data URI) no diretório de avatares
 * Realiza validações de formato (PNG/JPEG), tamanho e integridade
 *
 * @param string|null $dataUri           Data URI da imagem
 * @param string      $diretorioRelativo Caminho relativo para salvar
 * @param int         $tamanhoMaximoBytes Tamanho máximo em bytes (padrão 1MB)
 * @return string|null Nome do arquivo salvo ou null em caso de erro
 */
function salvarAvatarBase64(?string $dataUri, string $diretorioRelativo = '../uploads/avatares/', int $tamanhoMaximoBytes = 1048576): ?string
{
    $dataUri = trim((string) $dataUri);

    if ($dataUri === '') {
        return null;
    }

    // Aceita apenas PNG ou JPEG
    if (!preg_match('#^data:image/(png|jpeg);base64,#i', $dataUri)) {
        return null;
    }

    $base64 = substr($dataUri, strpos($dataUri, ',') + 1);
    $dadosImagem = base64_decode($base64, true);

    if ($dadosImagem === false || strlen($dadosImagem) > $tamanhoMaximoBytes) {
        return null;
    }

    $infoImagem = @getimagesizefromstring($dadosImagem);
    if ($infoImagem === false || empty($infoImagem['mime'])) {
        return null;
    }

    $mime = strtolower((string) $infoImagem['mime']);
    if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
        return null;
    }

    $diretorio = rtrim($diretorioRelativo, "/\\") . DIRECTORY_SEPARATOR;

    // Cria o diretório se não existir
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $extensao = $mime === 'image/png' ? 'png' : 'jpg';
    $nomeArquivo = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $extensao;
    $caminhoCompleto = $diretorio . $nomeArquivo;

    if (file_put_contents($caminhoCompleto, $dadosImagem) === false) {
        return null;
    }

    return $nomeArquivo;
}

/**
 * Analisa o prazo de entrega em relação à data atual e retorna
 * classe CSS, rótulo e detalhe para exibição no card do projeto
 *
 * @param string|null $dataEntrega Data no formato Y-m-d
 * @param string|null $status      Status do projeto (ex: 'Feito')
 * @return array|null Array com 'classe', 'rotulo', 'detalhe', 'dias' ou null
 */
function analisarPrazoEntrega(?string $dataEntrega, ?string $status = null): ?array
{
    $dataEntrega = trim((string) $dataEntrega);

    if ($dataEntrega === '') {
        return null;
    }

    $data = DateTimeImmutable::createFromFormat('!Y-m-d', $dataEntrega);
    $erros = DateTimeImmutable::getLastErrors();

    if (
        $data === false || ($erros !== false && (($erros['warning_count'] ?? 0) > 0 || ($erros['error_count'] ?? 0) > 0))
    ) {
        return null;
    }

    $hoje = new DateTimeImmutable('today');
    $dias = (int) $hoje->diff($data)->format('%r%a');
    $dataFormatada = $data->format('d/m/Y');

    // Se o projeto estiver concluído, exibe como "Concluido"
    if (trim((string) $status) === 'Feito') {
        return [
            'classe' => 'deadline-done',
            'rotulo' => 'Concluido',
            'detalhe' => 'Entrega em ' . $dataFormatada,
            'dias' => $dias,
        ];
    }

    // Casos de prazo vencido
    if ($dias < 0) {
        return [
            'classe' => 'deadline-overdue',
            'rotulo' => 'Atrasado',
            'detalhe' => 'Venceu ha ' . abs($dias) . ' dia' . (abs($dias) === 1 ? '' : 's'),
            'dias' => $dias,
        ];
    }

    // Vence hoje
    if ($dias === 0) {
        return [
            'classe' => 'deadline-today',
            'rotulo' => 'Entrega hoje',
            'detalhe' => 'Vence hoje em ' . $dataFormatada,
            'dias' => 0,
        ];
    }

    // Prazo curto: até 3 dias
    if ($dias <= 3) {
        return [
            'classe' => 'deadline-soon',
            'rotulo' => 'Prazo curto',
            'detalhe' => 'Faltam ' . $dias . ' dia' . ($dias === 1 ? '' : 's'),
            'dias' => $dias,
        ];
    }

    // Prazo em breve: até 7 dias
    if ($dias <= 7) {
        return [
            'classe' => 'deadline-upcoming',
            'rotulo' => 'Prazo em breve',
            'detalhe' => 'Faltam ' . $dias . ' dias',
            'dias' => $dias,
        ];
    }

    // Prazo tranquilo
    return [
        'classe' => 'deadline-ok',
        'rotulo' => 'No prazo',
        'detalhe' => 'Faltam ' . $dias . ' dias',
        'dias' => $dias,
    ];
}

/**
 * Atualiza o status de um projeto com base na contagem dos status de suas tarefas
 * - Se não houver tarefas ou todas estiverem "A Fazer" -> "A Fazer"
 * - Se todas estiverem "Feito" -> "Feito"
 * - Caso contrário -> "Fazendo"
 *
 * @param PDO    $pdo        Conexão com o banco
 * @param int    $idProjeto  ID do projeto
 * @param int|null $idUser   ID do usuário (se fornecida, restringe a atualização ao dono)
 * @return string Novo status do projeto
 */
function atualizarStatusProjeto($pdo, $idProjeto, $idUser = null) {

    $sql = "SELECT status, COUNT(*) AS total
            FROM tarefas
            WHERE id_projeto = ?
            GROUP BY status";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProjeto]);

    $contagens = [
        "A Fazer" => 0,
        "Fazendo"  => 0,
        "Feito"    => 0
    ];

    $totalTarefas = 0;

    foreach ($stmt->fetchAll(PDO::FETCH_OBJ) as $linha) {
        if (isset($contagens[$linha->status])) {
            $contagens[$linha->status] = (int) $linha->total;
            $totalTarefas += (int) $linha->total;
        }
    }

    if ($totalTarefas === 0 || $contagens["A Fazer"] === $totalTarefas) {
        $novoStatus = "A Fazer";
    } elseif ($contagens["Feito"] === $totalTarefas) {
        $novoStatus = "Feito";
    } else {
        $novoStatus = "Fazendo";
    }

    // Atualização no banco, opcionalmente filtrando pelo dono
    if ($idUser !== null) {
        $sql = "UPDATE projetos
                SET status = ?
                WHERE id_projeto = ?
                AND id_user = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $novoStatus,
            $idProjeto,
            $idUser
        ]);

        return $novoStatus;
    }

    $sql = "UPDATE projetos
            SET status = ?
            WHERE id_projeto = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $novoStatus,
        $idProjeto
    ]);

    return $novoStatus;
}

/**
 * Limita uma descrição (string) a um número máximo de linhas visuais,
 * com base em uma contagem aproximada de caracteres por linha.
 * Útil para exibir prévias no Kanban sem quebrar o layout.
 *
 * @param string $texto              Texto completo
 * @param int    $limiteLinhas       Máximo de linhas (padrão 6)
 * @param int    $caracteresPorLinha Caracteres estimados por linha (padrão 42)
 * @return string Texto truncado com " [...]" se houver corte
 */
function limitarDescricaoKanban($texto, $limiteLinhas = 6, $caracteresPorLinha = 42) {
    $texto = (string) $texto;
    $linhas = preg_split("/\r\n|\r|\n/", $texto);
    $resultado = [];
    $linhasUsadas = 0;
    $foiCortado = false;

    foreach ($linhas as $indice => $linha) {
        $tamanhoLinha = function_exists("mb_strlen")
            ? mb_strlen($linha, "UTF-8")
            : strlen($linha);

        // Quantas linhas visuais esta linha real ocupa
        $linhasNecessarias = max(1, (int) ceil($tamanhoLinha / $caracteresPorLinha));
        $linhasRestantes = $limiteLinhas - $linhasUsadas;

        if ($linhasRestantes <= 0) {
            $foiCortado = true;
            break;
        }

        // Se a linha inteira não couber, corta no limite de caracteres
        if ($linhasNecessarias > $linhasRestantes) {
            $limiteCaracteres = $linhasRestantes * $caracteresPorLinha;
            $resultado[] = function_exists("mb_substr")
                ? rtrim(mb_substr($linha, 0, $limiteCaracteres, "UTF-8"))
                : rtrim(substr($linha, 0, $limiteCaracteres));

            $foiCortado = true;
            break;
        }

        $resultado[] = $linha;
        $linhasUsadas += $linhasNecessarias;

        if ($linhasUsadas >= $limiteLinhas && $indice < count($linhas) - 1) {
            $foiCortado = true;
            break;
        }
    }

    $textoLimitado = rtrim(implode("\n", $resultado));

    return $foiCortado ? $textoLimitado . " [...]" : $textoLimitado;
}

/**
 * Renderiza o quadro Kanban de projetos ou tarefas
 *
 * @param array $items    Array de objetos (projetos ou tarefas) com status, etc.
 * @param string $tipo    'projeto' ou 'tarefa'
 * @param int    $idProjeto ID do projeto (apenas para tarefas, para link de "adicionar")
 */
function render(array $items, string $tipo = 'projeto', int $idProjeto = 0): void
{
    // Organiza itens nas colunas por status
    $colunas = [
        'A Fazer' => [],
        'Fazendo'  => [],
        'Feito'    => [],
    ];

    foreach ($items as $item) {
        $status = $item->status ?? 'A Fazer';
        if (!isset($colunas[$status])) {
            $status = 'A Fazer';
        }
        $colunas[$status][] = $item;
    }

    $idCampo = $tipo === 'tarefa' ? 'id_tarefa' : 'id_projeto';
    $nomeTipo = $tipo === 'tarefa' ? 'tarefa' : 'projeto';
    $textoLixeira = $tipo === 'tarefa'
        ? 'Solte aqui para excluir a tarefa'
        : 'Solte aqui para excluir o projeto';

    // Configuração visual das colunas
    $configColunas = [
        'A Fazer' => ['classe' => 'col-amber', 'icone' => '○'],
        'Fazendo' => ['classe' => 'col-blue',  'icone' => '◐'],
        'Feito'   => ['classe' => 'col-green', 'icone' => '●'],
    ];

    // Atalho para escapar strings
    $esc = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    ?>

    <div data-kanban-board data-tipo="<?= $esc($nomeTipo); ?>">
        <section class="nb-columns" aria-label="Itens por status">
            <?php foreach ($configColunas as $status => $config): ?>
                <?php $lista = $colunas[$status]; ?>
                <?php $colunaId = "kanban-" . $nomeTipo . "-" . preg_replace('/[^a-z0-9]+/i', '-', strtolower($status)); ?>

                <div
                    class="nb-col <?= $config['classe']; ?>"
                    data-kanban-column
                    data-status="<?= $esc($status); ?>"
                    role="region"
                    aria-labelledby="<?= $esc($colunaId); ?>"
                >
                    <div class="col-header">
                        <h2 class="col-title" id="<?= $esc($colunaId); ?>">
                            <span aria-hidden="true"><?= $config['icone']; ?></span>
                            <?= $esc($status); ?>
                        </h2>
                        <span class="col-count"><?= count($lista); ?></span>
                    </div>

                    <?php if (empty($lista)): ?>
                        <!-- Mensagem de coluna vazia com link para adicionar -->
                        <div class="card-empty" data-empty-message>
                            <?php if ($tipo === 'tarefa'): ?>
                                <a href="../crud_tarefas/adicionar.php?id_projeto=<?= $idProjeto; ?>">+ nenhuma tarefa aqui ainda</a>
                            <?php else: ?>
                                <a href="../crud_projetos/cadastro.php">+ nenhum projeto aqui ainda</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($lista as $item): ?>
                            <?php
                                $id = $item->{$idCampo};
                                $tituloItem = (string) ($item->titulo ?? 'Sem titulo');
                                $tituloId = "kanban-" . $nomeTipo . "-" . $id . "-titulo";

                                // Links específicos para projetos ou tarefas
                                if ($tipo === 'projeto') {
                                    $abrir  = "../crud_projetos/projeto.php?id=" . $id;
                                    $editar = "../crud_projetos/editar.php?id=" . $id;
                                    $excluir = "../crud_projetos/excluir.php";

                                    $total = (int) ($item->total_tarefas ?? 0);
                                    $feitas = (int) ($item->tarefas_concluidas ?? 0);
                                    $percentual = $total > 0 ? (int) round(($feitas / $total) * 100) : 0;
                                    $prazo = analisarPrazoEntrega($item->data_entrega ?? null, $item->status ?? null);
                                } else {
                                    $abrir = null;
                                    $editar = "../crud_tarefas/editar.php?id=" . $id;
                                    $excluir = "../crud_tarefas/excluir.php";
                                }

                                // Limita a descrição para exibição no card
                                $descricao = $item->descricao ?? '';
                                if ($descricao !== '' && function_exists('limitarDescricaoKanban')) {
                                    $descricao = limitarDescricaoKanban($descricao);
                                }

                                $subtarefas = [];
                                if ($tipo === 'tarefa' && isset($item->subtarefas) && is_array($item->subtarefas)) {
                                    $subtarefas = $item->subtarefas;
                                }
                            ?>

                            <article
                                class="kanban-item-card kanban-card <?= $tipo === 'tarefa' ? 'kanban-task-card' : 'kanban-project-card'; ?>"
                                data-kanban-card
                                data-id="<?= $esc($id); ?>"
                                data-tipo="<?= $esc($nomeTipo); ?>"
                                aria-labelledby="<?= $esc($tituloId); ?>"
                            >
                                <h3 class="card-name" id="<?= $esc($tituloId); ?>">
                                    <?= $esc($item->titulo ?? 'Sem título'); ?>
                                </h3>

                                <!-- Barra de progresso para projetos -->
                                <?php if ($tipo === 'projeto' && $total > 0): ?>
                                    <div class="card-progress-wrap">
                                        <div class="card-progress-bar" aria-hidden="true">
                                            <div
                                                class="card-progress-fill"
                                                style="width: <?= $percentual; ?>%;"
                                                data-target="<?= $percentual; ?>"
                                            ></div>
                                        </div>
                                        <span class="card-progress-label">
                                            <?= $feitas; ?> de <?= $total; ?> tarefas — <?= $percentual; ?>%
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Prazo de entrega (apenas para projetos) -->
                                <?php if ($tipo === 'projeto'): ?>
                                    <div class="deadline-pill <?= !empty($prazo) ? $esc($prazo['classe']) : 'deadline-muted'; ?>">
                                        <span class="deadline-pill__label">
                                            <?= !empty($prazo) ? $esc($prazo['rotulo']) : 'Sem prazo'; ?>
                                        </span>
                                        <span class="deadline-pill__detail">
                                            <?= !empty($prazo) ? $esc($prazo['detalhe']) : 'Defina uma data de entrega'; ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Descrição truncada da tarefa -->
                                <?php if ($tipo === 'tarefa' && $descricao !== ''): ?>
                                    <p class="card-desc" title="<?= $esc($item->descricao ?? ''); ?>">
                                        <?= nl2br($esc($descricao)); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Prioridade (tarefas) -->
                                <?php if ($tipo === 'tarefa' && isset($item->prioridade)): ?>
                                    <div class="card-priority-wrap">
                                        <span class="priority-pill">
                                            Prioridade: <?= $esc($item->prioridade); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <!-- Bloco de subtarefas (apenas tarefas) -->
                                <?php if ($tipo === 'tarefa'): ?>
                                    <?php
                                        $totalSubtarefas = count($subtarefas);
                                        $subtarefasConcluidas = 0;

                                        foreach ($subtarefas as $subtarefa) {
                                            if ((int) ($subtarefa->concluida ?? 0) === 1) {
                                                $subtarefasConcluidas++;
                                            }
                                        }
                                    ?>

                                    <div
                                        class="subtasks"
                                        data-subtasks
                                        data-task-id="<?= $esc($id); ?>"
                                    >
                                        <div class="subtasks-header">
                                            <span class="subtasks-title">
                                                Subtarefas
                                                <span data-subtasks-count><?= $subtarefasConcluidas; ?>/<?= $totalSubtarefas; ?></span>
                                            </span>

                                            <button
                                                type="button"
                                                class="subtask-add-toggle"
                                                data-subtask-add-toggle
                                                aria-label="Adicionar subtarefa"
                                            >
                                                +
                                            </button>
                                        </div>

                                        <div class="subtasks-list" data-subtasks-list>
                                            <?php foreach ($subtarefas as $subtarefa): ?>
                                                <div
                                                    class="subtask-item"
                                                    data-subtask-item
                                                    data-subtask-id="<?= $esc($subtarefa->id_subtarefa ?? ''); ?>"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        class="subtask-checkbox"
                                                        data-subtask-checkbox
                                                        aria-label="Marcar subtarefa <?= $esc($subtarefa->titulo ?? ''); ?> como concluida"
                                                        <?= ((int) ($subtarefa->concluida ?? 0) === 1) ? 'checked' : ''; ?>
                                                    >

                                                    <span class="subtask-title <?= ((int) ($subtarefa->concluida ?? 0) === 1) ? 'is-done' : ''; ?>" data-subtask-title>
                                                        <?= $esc($subtarefa->titulo ?? ''); ?>
                                                    </span>

                                                    <button
                                                        type="button"
                                                        class="subtask-delete"
                                                        data-subtask-delete
                                                        aria-label="Excluir subtarefa <?= $esc($subtarefa->titulo ?? ''); ?>"
                                                    >
                                                        x
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Formulário de nova subtarefa (oculto por padrão) -->
                                        <form class="subtask-form hidden" data-subtask-form>
                                            <input
                                                type="text"
                                                name="titulo"
                                                class="subtask-input"
                                                placeholder="Nova subtarefa"
                                                maxlength="150"
                                                data-subtask-input
                                            >

                                            <button type="submit" class="subtask-save">
                                                Salvar
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Ações do card: abrir, editar, excluir -->
                                <div class="card-meta">
                                    <?php if ($tipo === 'projeto'): ?>
                                        <a
                                            href="<?= $esc($abrir); ?>"
                                            class="card-action card-action-primary"
                                            aria-label="Abrir projeto <?= $esc($tituloItem); ?>"
                                        >
                                            Abrir
                                        </a>
                                    <?php endif; ?>

                                    <a
                                        href="<?= $esc($editar); ?>"
                                        class="card-action card-action-warning"
                                        aria-label="Editar <?= $esc($nomeTipo); ?> <?= $esc($tituloItem); ?>"
                                    >
                                        Editar
                                    </a>

                                    <form
                                        action="<?= $esc($excluir); ?>"
                                        method="POST"
                                        class="card-delete-form"
                                        onsubmit="return confirm('Deseja excluir este item?')"
                                    >
                                        <?= csrfInput(); ?>
                                        <input type="hidden" name="id" value="<?= $esc($id); ?>">
                                        <button
                                            type="submit"
                                            class="card-action card-action-danger"
                                            aria-label="Excluir <?= $esc($nomeTipo); ?> <?= $esc($tituloItem); ?>"
                                        >
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>

        <!-- Área de lixeira para exclusão via drag-and-drop -->
        <div class="kanban-trash nb-trash" data-kanban-trash>
            <?= $esc($textoLixeira); ?>
        </div>
    </div>

    <?php
}