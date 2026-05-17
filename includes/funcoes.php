<?php

function atualizarStatusProjeto($pdo, $idProjeto, $idUser = null) {

    $sql = "SELECT status, COUNT(*) AS total
            FROM tarefas
            WHERE id_projeto = ?
            GROUP BY status";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$idProjeto]);

    $contagens = [
        "A Fazer" => 0,
        "Fazendo" => 0,
        "Feito" => 0
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

function render($items, $tipo = "projeto") {

    $a_fazer = [];
    $fazendo = [];
    $feito = [];

    foreach($items as $item) {

        if($item->status == "A Fazer") {
            $a_fazer[] = $item;
        }

        elseif($item->status == "Fazendo") {
            $fazendo[] = $item;
        }

        elseif($item->status == "Feito") {
            $feito[] = $item;
        }

    }

    $colunas = [
        "A Fazer" => $a_fazer,
        "Fazendo" => $fazendo,
        "Feito" => $feito
    ];

    $idCampo = $tipo == "tarefa" ? "id_tarefa" : "id_projeto";
    $nomeTipo = $tipo == "tarefa" ? "tarefa" : "projeto";
    $textoLixeira = $tipo == "tarefa" ? "Solte aqui para excluir a tarefa" : "Solte aqui para excluir o projeto";

    ?>

    <div class="kanban-board" data-kanban-board data-tipo="<?= $nomeTipo; ?>">

    <div class="grid gap-4 md:grid-cols-3">

        <?php foreach($colunas as $status => $lista): ?>

            <div>

                <div class="<?= ui_card("h-full"); ?>">

                    <div class="<?=

                        $status == "A Fazer"
                        ? ui_column_header("todo")

                        : ($status == "Fazendo"
                            ? ui_column_header("doing")
                            : ui_column_header("done")
                        );

                    ?>">

                        <h2 class="text-lg font-semibold">
                            <?= $status; ?>
                        </h2>

                    </div>

                    <div
                        class="<?= ui_card_body("kanban-column min-h-28"); ?>"
                        data-kanban-column
                        data-status="<?= htmlspecialchars($status); ?>"
                    >

                        <?php if(count($lista) > 0): ?>

                            <?php foreach($lista as $item): ?>

                                <?php

                                if($tipo == "projeto") {

                                    $abrir = "../crud_projetos/projeto.php?id=" . $item->id_projeto;

                                    $editar = "../crud_projetos/editar.php?id=" . $item->id_projeto;

                                    $excluir = "../crud_projetos/excluir.php?id=" . $item->id_projeto;

                                }

                                elseif($tipo == "tarefa") {

                                    $abrir = "#";

                                    $editar = "../crud_tarefas/editar.php?id=" . $item->id_tarefa;

                                    $excluir = "../crud_tarefas/excluir.php?id=" . $item->id_tarefa;

                                }

                                ?>

                                <div
                                    class="<?= ui_card("kanban-card mb-3 cursor-grab active:cursor-grabbing"); ?>"
                                    data-kanban-card
                                    data-id="<?= htmlspecialchars($item->{$idCampo}); ?>"
                                    data-tipo="<?= $nomeTipo; ?>"
                                >

                                    <div class="<?= ui_card_body(); ?>">

                                        <h3 class="mb-2 text-base font-semibold text-slate-900">
                                            <?= htmlspecialchars($item->titulo); ?>
                                        </h3>

                                        <p class="text-sm text-slate-600">
                                            <?= htmlspecialchars($item->descricao); ?>
                                        </p>

                                        <?php if($tipo == "tarefa" && isset($item->prioridade)): ?>

                                            <span class="mt-3 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                                Prioridade: <?= htmlspecialchars($item->prioridade); ?>
                                            </span>

                                        <?php endif; ?>

                                        <?php if($tipo == "tarefa"): ?>

                                            <?php
                                                $subtarefas = $item->subtarefas ?? [];
                                                $totalSubtarefas = count($subtarefas);
                                                $subtarefasConcluidas = 0;

                                                foreach($subtarefas as $subtarefa) {
                                                    if((int) $subtarefa->concluida === 1) {
                                                        $subtarefasConcluidas++;
                                                    }
                                                }
                                            ?>

                                            <div
                                                class="subtasks mt-4 border-t border-slate-100 pt-3"
                                                data-subtasks
                                                data-task-id="<?= htmlspecialchars($item->id_tarefa); ?>"
                                            >

                                                <div class="mb-2 flex items-center justify-between gap-2">
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                        Subtarefas
                                                        <span data-subtasks-count><?= $subtarefasConcluidas; ?>/<?= $totalSubtarefas; ?></span>
                                                    </span>

                                                    <button
                                                        type="button"
                                                        class="subtask-add-toggle inline-flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 bg-white text-base font-semibold leading-none text-slate-700 hover:bg-slate-50"
                                                        data-subtask-add-toggle
                                                        aria-label="Adicionar subtarefa"
                                                    >
                                                        +
                                                    </button>
                                                </div>

                                                <div class="space-y-1" data-subtasks-list>
                                                    <?php foreach($subtarefas as $subtarefa): ?>
                                                        <div
                                                            class="subtask-item flex items-start gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-slate-50"
                                                            data-subtask-item
                                                            data-subtask-id="<?= htmlspecialchars($subtarefa->id_subtarefa); ?>"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                class="subtask-checkbox mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                                                                data-subtask-checkbox
                                                                <?= (int) $subtarefa->concluida === 1 ? "checked" : ""; ?>
                                                            >

                                                            <span class="min-w-0 flex-1 text-slate-700 <?= (int) $subtarefa->concluida === 1 ? "line-through text-slate-400" : ""; ?>" data-subtask-title>
                                                                <?= htmlspecialchars($subtarefa->titulo); ?>
                                                            </span>

                                                            <button
                                                                type="button"
                                                                class="subtask-delete text-xs font-semibold text-rose-600 hover:text-rose-700"
                                                                data-subtask-delete
                                                                aria-label="Excluir subtarefa"
                                                            >
                                                                x
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                                <form class="mt-2 hidden gap-2" data-subtask-form>
                                                    <input
                                                        type="text"
                                                        name="titulo"
                                                        class="<?= ui_input("py-1.5 text-xs"); ?>"
                                                        placeholder="Nova subtarefa"
                                                        maxlength="150"
                                                        data-subtask-input
                                                    >

                                                    <button type="submit" class="<?= ui_button("primary", "sm"); ?>">
                                                        Salvar
                                                    </button>
                                                </form>

                                            </div>

                                        <?php endif; ?>

                                        <div class="mt-4 flex flex-wrap gap-2">

                                            <?php if($tipo == "projeto"): ?>

                                                <a
                                                    href="<?= $abrir; ?>"
                                                    class="<?= ui_button("primary", "sm"); ?>"
                                                >
                                                    Abrir
                                                </a>

                                            <?php endif; ?>

                                            <a
                                                href="<?= $editar; ?>"
                                                class="<?= ui_button("warning", "sm"); ?>"
                                            >
                                                Editar
                                            </a>

                                            <a
                                                href="<?= $excluir; ?>"
                                                class="<?= ui_button("danger", "sm"); ?>"
                                                onclick="return confirm('Deseja excluir este item?')"
                                            >
                                                Excluir
                                            </a>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <p class="text-sm text-slate-500" data-empty-message>
                                Nenhum item.
                            </p>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <div
        class="kanban-trash mt-4 flex min-h-20 items-center justify-center rounded-lg border-2 border-dashed border-rose-300 bg-rose-50 px-4 py-5 text-center text-sm font-medium text-rose-700 transition"
        data-kanban-trash
    >
        <?= $textoLixeira; ?>
    </div>

    </div>

    <?php

}
