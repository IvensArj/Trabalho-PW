(function () {
    function updateEmptyMessages(board) {
        board.querySelectorAll("[data-kanban-column]").forEach(function (column) {
            var hasCards = column.querySelector("[data-kanban-card]") !== null;
            var emptyMessage = column.querySelector("[data-empty-message]");

            if (emptyMessage) {
                emptyMessage.hidden = hasCards;
            }
        });
    }

    function requestKanbanAction(payload) {
        return fetch(window.KANBAN_ENDPOINT || "../includes/kanban_acoes.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams(payload)
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.ok) {
                    throw new Error(data.mensagem || "Nao foi possivel salvar a alteracao.");
                }

                return data;
            });
        });
    }

    function setCardLoading(card, loading) {
        card.classList.toggle("opacity-60", loading);
        card.classList.toggle("pointer-events-none", loading);
    }

    function restoreCard(card, originalParent, originalNextSibling) {
        if (!originalParent) {
            return;
        }

        if (originalNextSibling && originalNextSibling.parentElement === originalParent) {
            originalParent.insertBefore(card, originalNextSibling);
            return;
        }

        originalParent.appendChild(card);
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (typeof Sortable === "undefined") {
            return;
        }

        document.querySelectorAll("[data-kanban-board]").forEach(function (board) {
            var tipo = board.dataset.tipo;
            var dragState = {
                parent: null,
                nextSibling: null
            };

            updateEmptyMessages(board);

            board.querySelectorAll("[data-kanban-column]").forEach(function (column) {
                new Sortable(column, {
                    group: "kanban-" + tipo,
                    animation: 160,
                    draggable: "[data-kanban-card]",
                    filter: "a, button, input, textarea, select, [data-empty-message], [data-subtasks], [data-subtasks] *",
                    preventOnFilter: false,
                    ghostClass: "kanban-card-ghost",
                    chosenClass: "kanban-card-chosen",
                    dragClass: "kanban-card-drag",

                    onStart: function (event) {
                        dragState.parent = event.from;
                        dragState.nextSibling = event.item.nextElementSibling;
                        dragState.status = event.from.dataset.status || null;
                        board.classList.add("kanban-dragging");
                    },

                    onEnd: function (event) {
                        board.classList.remove("kanban-dragging");

                        if (!event.to.hasAttribute("data-kanban-column")) {
                            updateEmptyMessages(board);
                            return;
                        }

                        var card = event.item;
                        var novoStatus = event.to.dataset.status;

                        updateEmptyMessages(board);

                        if (novoStatus === dragState.status) {
                            return;
                        }

                        setCardLoading(card, true);

                        requestKanbanAction({
                            acao: "atualizar_status",
                            tipo: card.dataset.tipo,
                            id: card.dataset.id,
                            status: novoStatus
                        }).then(function (data) {
                            var projectStatus = document.querySelector("[data-project-status-value]");

                            if (projectStatus && data.project_status) {
                                projectStatus.textContent = data.project_status;
                            }
                        }).catch(function (error) {
                            restoreCard(card, dragState.parent, dragState.nextSibling);
                            updateEmptyMessages(board);
                            alert(error.message);
                        }).finally(function () {
                            setCardLoading(card, false);
                        });
                    }
                });
            });

            var trash = board.querySelector("[data-kanban-trash]");

            if (trash) {
                new Sortable(trash, {
                    group: "kanban-" + tipo,
                    animation: 160,
                    draggable: "[data-kanban-card]",
                    ghostClass: "kanban-card-ghost",
                    chosenClass: "kanban-card-chosen",

                    onAdd: function (event) {
                        var card = event.item;

                        if (!confirm("Deseja excluir este item?")) {
                            restoreCard(card, dragState.parent, dragState.nextSibling);
                            updateEmptyMessages(board);
                            return;
                        }

                        setCardLoading(card, true);

                        requestKanbanAction({
                            acao: "excluir",
                            tipo: card.dataset.tipo,
                            id: card.dataset.id
                        }).then(function (data) {
                            var projectStatus = document.querySelector("[data-project-status-value]");

                            if (projectStatus && data.project_status) {
                                projectStatus.textContent = data.project_status;
                            }

                            card.remove();
                            updateEmptyMessages(board);
                        }).catch(function (error) {
                            restoreCard(card, dragState.parent, dragState.nextSibling);
                            updateEmptyMessages(board);
                            alert(error.message);
                        }).finally(function () {
                            setCardLoading(card, false);
                            board.classList.remove("kanban-dragging");
                        });
                    }
                });
            }
        });
    });
}());
