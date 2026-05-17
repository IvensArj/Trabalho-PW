(function () {
    function requestSubtaskAction(payload) {
        return fetch(window.SUBTASKS_ENDPOINT || "../includes/subtarefas_acoes.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams(payload)
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok || !data.ok) {
                    throw new Error(data.mensagem || "Nao foi possivel salvar a subtarefa.");
                }

                return data;
            });
        });
    }

    function escapeHtml(value) {
        var div = document.createElement("div");
        div.textContent = value;
        return div.innerHTML;
    }

    function updateCount(container) {
        var items = container.querySelectorAll("[data-subtask-item]");
        var checked = container.querySelectorAll("[data-subtask-checkbox]:checked");
        var count = container.querySelector("[data-subtasks-count]");

        if (count) {
            count.textContent = checked.length + "/" + items.length;
        }
    }

    function createSubtaskElement(subtask) {
        var wrapper = document.createElement("div");

        wrapper.className = "subtask-item flex items-start gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-slate-50";
        wrapper.dataset.subtaskItem = "";
        wrapper.dataset.subtaskId = subtask.id_subtarefa;
        wrapper.innerHTML = [
            '<input type="checkbox" class="subtask-checkbox mt-0.5 h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500" data-subtask-checkbox>',
            '<span class="min-w-0 flex-1 text-slate-700" data-subtask-title>' + escapeHtml(subtask.titulo) + '</span>',
            '<button type="button" class="subtask-delete text-xs font-semibold text-rose-600 hover:text-rose-700" data-subtask-delete aria-label="Excluir subtarefa">x</button>'
        ].join("");

        return wrapper;
    }

    function setItemDone(item, done) {
        var checkbox = item.querySelector("[data-subtask-checkbox]");
        var title = item.querySelector("[data-subtask-title]");

        if (checkbox) {
            checkbox.checked = done;
        }

        if (title) {
            title.classList.toggle("line-through", done);
            title.classList.toggle("text-slate-400", done);
            title.classList.toggle("text-slate-700", !done);
        }
    }

    document.addEventListener("click", function (event) {
        var toggle = event.target.closest("[data-subtask-add-toggle]");
        var deleteButton = event.target.closest("[data-subtask-delete]");

        if (toggle) {
            var container = toggle.closest("[data-subtasks]");
            var form = container.querySelector("[data-subtask-form]");
            var input = container.querySelector("[data-subtask-input]");

            form.classList.toggle("hidden");
            form.classList.toggle("flex");

            if (!form.classList.contains("hidden")) {
                input.focus();
            }
        }

        if (deleteButton) {
            var item = deleteButton.closest("[data-subtask-item]");
            var container = deleteButton.closest("[data-subtasks]");

            if (!confirm("Deseja excluir esta subtarefa?")) {
                return;
            }

            item.classList.add("opacity-60", "pointer-events-none");

            requestSubtaskAction({
                acao: "excluir",
                id_subtarefa: item.dataset.subtaskId
            }).then(function () {
                item.remove();
                updateCount(container);
            }).catch(function (error) {
                item.classList.remove("opacity-60", "pointer-events-none");
                alert(error.message);
            });
        }
    });

    document.addEventListener("change", function (event) {
        var checkbox = event.target.closest("[data-subtask-checkbox]");

        if (!checkbox) {
            return;
        }

        var item = checkbox.closest("[data-subtask-item]");
        var container = checkbox.closest("[data-subtasks]");
        var checked = checkbox.checked;

        setItemDone(item, checked);
        updateCount(container);
        item.classList.add("opacity-60");

        requestSubtaskAction({
            acao: "alternar",
            id_subtarefa: item.dataset.subtaskId,
            concluida: checked ? "1" : "0"
        }).catch(function (error) {
            setItemDone(item, !checked);
            updateCount(container);
            alert(error.message);
        }).finally(function () {
            item.classList.remove("opacity-60");
        });
    });

    document.addEventListener("submit", function (event) {
        var form = event.target.closest("[data-subtask-form]");

        if (!form) {
            return;
        }

        event.preventDefault();

        var container = form.closest("[data-subtasks]");
        var list = container.querySelector("[data-subtasks-list]");
        var input = container.querySelector("[data-subtask-input]");
        var title = input.value.trim();
        var button = form.querySelector("button[type='submit']");

        if (!title) {
            input.focus();
            return;
        }

        input.disabled = true;
        button.disabled = true;

        requestSubtaskAction({
            acao: "criar",
            id_tarefa: container.dataset.taskId,
            titulo: title
        }).then(function (data) {
            list.appendChild(createSubtaskElement(data.subtarefa));
            input.value = "";
            updateCount(container);
            input.focus();
        }).catch(function (error) {
            alert(error.message);
        }).finally(function () {
            input.disabled = false;
            button.disabled = false;
        });
    });
}());
