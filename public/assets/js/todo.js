document.addEventListener('DOMContentLoaded', function () {
    const todoList = document.getElementById('todoList');
    const dateInput = document.getElementById('taskDate');
    const taskTitle = document.getElementById('taskTitle');
    const config = window.todoConfig;

    let taskIdToDelete = null;
    const deleteModalEl = document.getElementById('deleteTaskModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    /* ---------------- Sound System ---------------- */
    const sounds = {
        check: new Audio(config.sounds.check),
        uncheck: config.sounds.uncheck ? new Audio(config.sounds.uncheck) : null,
        celebrate: new Audio(config.sounds.celebrate)
    };
    Object.values(sounds).forEach(s => s?.load());
    let soundEnabled = localStorage.getItem('todoSound') !== 'off';
    let soundVolume = Number(localStorage.getItem('todoVolume')) || 0.5;
    Object.values(sounds).forEach(s => { if (s) s.volume = soundVolume; });

    /* ---------------- Helpers ---------------- */
    function fetchJson(url, options = {}) {
        return fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrf
            },
            ...options
        }).then(res => res.json());
    }

    function loadTasks(date) {
        fetch(`${config.routes.byDate}?date=${date}`)
            .then(res => res.json())
            .then(res => {
                todoList.innerHTML = '';

                res.data.forEach(task => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item todo-item';
                    li.dataset.id = task.id;

                    li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check flex-grow-1">
                                <input class="form-check-input toggle-done"
                                    type="checkbox"
                                    data-id="${task.id}"
                                    ${task.done ? 'checked' : ''}>
                                <span class="task-title ${task.done ? 'done' : ''}"
                                    data-id="${task.id}">
                                    ${task.title}
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-sm btn-outline-secondary edit-task"
                                    data-id="${task.id}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-task"
                                    data-id="${task.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <i class="bi bi-grip-vertical drag-handle"></i>
                            </div>
                        </div>
                    `;
                    todoList.appendChild(li);
                });
            });
    }

    /* ---------------- Init ---------------- */
    const today = new Date().toISOString().slice(0, 10);
    dateInput.value = today;
    loadTasks(today);

    /* ---------------- Events ---------------- */
    dateInput.addEventListener('change', () => loadTasks(dateInput.value));

    document.getElementById('addTaskForm').addEventListener('submit', e => {
        e.preventDefault();
        fetchJson(config.routes.store, {
            method: 'POST',
            body: JSON.stringify({ title: taskTitle.value })
        }).then(() => {
            taskTitle.value = '';
            loadTasks(dateInput.value);
        });
    });

    // Toggle done with sound & haptic
    todoList.addEventListener('change', e => {
        if (!e.target.classList.contains('toggle-done')) return;

        const checkbox = e.target;

        // Play sound
        if (soundEnabled) {
            if (checkbox.checked) {
                sounds.check.currentTime = 0;
                sounds.check.play().catch(() => { });
            } else if (sounds.uncheck) {
                sounds.uncheck.currentTime = 0;
                sounds.uncheck.play().catch(() => { });
            }
        }

        // Haptic feedback
        if (navigator.vibrate) navigator.vibrate(checkbox.checked ? 30 : 15);

        fetchJson(config.routes.toggle, {
            method: 'PUT',
            body: JSON.stringify({
                task_id: checkbox.dataset.id,
                date: dateInput.value
            })
        }).then(() => {
            loadTasks(dateInput.value);
            checkCelebrate();
        });
    });

    // Edit task
    todoList.addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-task');
        if (!editBtn) return;

        const taskId = editBtn.dataset.id;
        const span = todoList.querySelector(`.task-title[data-id="${taskId}"]`);
        if (!span) return;

        const input = document.createElement('input');
        input.className = 'edit-input';
        input.value = span.textContent.trim();
        input.dataset.id = taskId;

        span.replaceWith(input);
        input.focus();
        input.select();
    });

    // Open delete popup
    todoList.addEventListener('click', e => {
        const deleteBtn = e.target.closest('.delete-task');
        if (!deleteBtn) return;

        taskIdToDelete = deleteBtn.dataset.id;
        deleteModal.show();
    });

    // Confirm delete
    document.getElementById('confirmDeleteTask').addEventListener('click', function () {
        if (!taskIdToDelete) return;

        this.disabled = true;

        fetchJson(config.routes.delete, {
            method: 'DELETE',
            body: JSON.stringify({ task_id: taskIdToDelete })
        }).then(() => {
            this.disabled = false;
            taskIdToDelete = null;
            deleteModal.hide();
            loadTasks(dateInput.value);
        });
    });

    function saveEdit(input) {
        const title = input.value.trim();
        if (!title) return loadTasks(dateInput.value);

        fetchJson(config.routes.edit, {
            method: 'PUT',
            body: JSON.stringify({
                task_id: input.dataset.id,
                title
            })
        }).then(() => loadTasks(dateInput.value));
    }

    todoList.addEventListener('keydown', e => {
        if (!e.target.classList.contains('edit-input')) return;
        if (e.key === 'Enter') saveEdit(e.target);
        if (e.key === 'Escape') loadTasks(dateInput.value);
    });

    todoList.addEventListener('blur', e => {
        if (e.target.classList.contains('edit-input')) {
            saveEdit(e.target);
        }
    }, true);

    /* ---------------- Drag & Drop ---------------- */
    new Sortable(todoList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd() {
            const order = [...todoList.children].map((el, i) => ({
                id: el.dataset.id,
                priority: i + 1
            }));

            fetchJson(config.routes.reorder, {
                method: 'PUT',
                body: JSON.stringify({ order })
            });
        }
    });

    /* ---------------- Celebration ---------------- */
    function checkCelebrate() {
        const checkboxes = todoList.querySelectorAll('.toggle-done');
        if (!checkboxes.length) return;

        const allDone = [...checkboxes].every(cb => cb.checked);

        if (allDone && soundEnabled) {
            // Play celebrate sound
            sounds.celebrate.currentTime = 0;
            sounds.celebrate.play().catch(() => { });

            const container = document.getElementById('celebration-container');

            const shapes = ['square', 'circle', 'triangle'];

            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');

                // Random size
                const size = 5 + Math.random() * 8;
                confetti.style.width = `${size}px`;
                confetti.style.height = `${size}px`;

                // Random color
                confetti.style.backgroundColor = `hsl(${Math.random() * 360}, 70%, 60%)`;

                // Random shape
                const shape = shapes[Math.floor(Math.random() * shapes.length)];
                if (shape === 'circle') confetti.style.borderRadius = '50%';
                if (shape === 'triangle') {
                    confetti.style.width = '0';
                    confetti.style.height = '0';
                    confetti.style.borderLeft = `${size / 2}px solid transparent`;
                    confetti.style.borderRight = `${size / 2}px solid transparent`;
                    confetti.style.borderBottom = `${size}px solid ${confetti.style.backgroundColor}`;
                    confetti.style.backgroundColor = 'transparent';
                }

                confetti.style.left = `${Math.random() * 100}%`;
                confetti.style.animationDuration = `${3 + Math.random() * 1}s`; // falls in 3-4s
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

                container.appendChild(confetti);

                // Remove after animation
                setTimeout(() => confetti.remove(), 4000); // matches 4s sound
            }
        }
    }
});
