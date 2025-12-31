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
    function notifyError(message) {
        toastr.error(message, 'Error', { timeOut: 3000, closeButton: true });
    }

    async function fetchJson(url, options = {}) {
        try {
            const res = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.config.csrf
                },
                ...options
            });
            const data = await res.json();

            if (!res.ok || data.status >= 400) {
                notifyError(data.message || 'Something went wrong');
                return Promise.reject(data);
            }

            return data;
        } catch (err) {
            notifyError('Network or server error');
            return Promise.reject(err);
        }
    }

    function loadTasks(date) {
        fetchJson(`${config.routes.byDate}?date=${date}`)
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
            })
            .catch(() => { }); // errors already notified
    }

    /* ---------------- Init ---------------- */
    const now = new Date();
    // Get YYYY-MM-DD in local Cairo time
    const today = now.toLocaleDateString('en-CA', { timeZone: 'Africa/Cairo' });
    dateInput.value = today;

    loadTasks(today);

    /* ---------------- Events ---------------- */
    dateInput.addEventListener('change', () => loadTasks(dateInput.value));

    // Add Task
    document.getElementById('addTaskForm').addEventListener('submit', e => {
        e.preventDefault();
        const title = taskTitle.value.trim();
        if (!title) return;

        fetchJson(config.routes.store, {
            method: 'POST',
            body: JSON.stringify({ title })
        }).then(() => {
            taskTitle.value = '';
            loadTasks(dateInput.value);
        }).catch(() => { });
    });

    // Toggle done with sound & haptic
    todoList.addEventListener('change', e => {
        if (!e.target.classList.contains('toggle-done')) return;

        const checkbox = e.target;
        const previousState = !checkbox.checked; // rollback state if failed

        fetchJson(config.routes.toggle, {
            method: 'PUT',
            body: JSON.stringify({
                task_id: checkbox.dataset.id,
                date: dateInput.value
            })
        }).then(() => {
            // Play sound only on success
            if (soundEnabled) {
                if (checkbox.checked) {
                    sounds.check.currentTime = 0;
                    sounds.check.play().catch(() => { });
                } else if (sounds.uncheck) {
                    sounds.uncheck.currentTime = 0;
                    sounds.uncheck.play().catch(() => { });
                }
            }

            if (navigator.vibrate) navigator.vibrate(checkbox.checked ? 30 : 15);
            loadTasks(dateInput.value);
            checkCelebrate();
        }).catch(() => {
            // rollback checkbox
            checkbox.checked = previousState;
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

    function saveEdit(input) {
        const title = input.value.trim();
        if (!title) return loadTasks(dateInput.value);

        const oldValue = input.value;
        fetchJson(config.routes.edit, {
            method: 'PUT',
            body: JSON.stringify({
                task_id: input.dataset.id,
                title
            })
        }).then(() => {
            loadTasks(dateInput.value);
        }).catch(() => { input.value = oldValue; });
    }

    todoList.addEventListener('keydown', e => {
        if (!e.target.classList.contains('edit-input')) return;
        if (e.key === 'Enter') saveEdit(e.target);
        if (e.key === 'Escape') loadTasks(dateInput.value);
    });

    todoList.addEventListener('blur', e => {
        if (e.target.classList.contains('edit-input')) saveEdit(e.target);
    }, true);

    // Delete task
    todoList.addEventListener('click', e => {
        const deleteBtn = e.target.closest('.delete-task');
        if (!deleteBtn) return;
        taskIdToDelete = deleteBtn.dataset.id;
        deleteModal.show();
    });

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
        }).catch(() => { this.disabled = false; });
    });

    // Drag & Drop
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
            }).catch(() => { });
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

                const size = 5 + Math.random() * 8;
                confetti.style.width = `${size}px`;
                confetti.style.height = `${size}px`;
                confetti.style.backgroundColor = `hsl(${Math.random() * 360},70%,60%)`;

                const shape = shapes[Math.floor(Math.random() * shapes.length)];
                if (shape === 'circle') confetti.style.borderRadius = '50%';
                if (shape === 'triangle') {
                    confetti.style.width = '0'; confetti.style.height = '0';
                    confetti.style.borderLeft = `${size / 2}px solid transparent`;
                    confetti.style.borderRight = `${size / 2}px solid transparent`;
                    confetti.style.borderBottom = `${size}px solid ${confetti.style.backgroundColor}`;
                    confetti.style.backgroundColor = 'transparent';
                }

                confetti.style.left = `${Math.random() * 100}%`;
                confetti.style.animationDuration = `${3 + Math.random()}s`;
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;

                container.appendChild(confetti);
                setTimeout(() => confetti.remove(), 4000);
            }
        }
    }

    /*------------------------- Full Text Popup -------------------------*/
    let activePopup = null;

    todoList.addEventListener('click', e => {
        const title = e.target.closest('.task-title');
        if (!title) return;

        // ❌ Don't show popup if editing
        if (title.closest('.todo-item')?.querySelector('.edit-input')) return;

        // ❌ Only show if text is truncated
        if (title.scrollWidth <= title.clientWidth) return;

        // Remove previous popup
        if (activePopup) {
            activePopup.remove();
            activePopup = null;
        }

        // Create popup
        const popup = document.createElement('div');
        popup.className = 'task-popup';
        popup.textContent = title.textContent.trim();
        document.body.appendChild(popup);

        // Get bounding rect of title
        const rect = title.getBoundingClientRect();
        const isRTL = getComputedStyle(title).direction === 'rtl';

        let top = rect.bottom + 5; // below text
        let left;

        if (isRTL) {
            // Position from the right edge of title
            left = rect.right - popup.offsetWidth;
            if (left < 5) left = 5; // prevent overflow left
        } else {
            left = rect.left;
            const overflowRight = left + popup.offsetWidth - window.innerWidth;
            if (overflowRight > 0) left -= (overflowRight + 5); // prevent overflow right
        }

        // Keep inside viewport vertically
        const overflowBottom = top + popup.offsetHeight - window.innerHeight;
        if (overflowBottom > 0) top = rect.top - popup.offsetHeight - 5;

        popup.style.top = `${top}px`;
        popup.style.left = `${left}px`;

        activePopup = popup;

        e.stopPropagation();
    });

    // Auto-close popup on edit button click
    todoList.addEventListener('click', e => {
        if (e.target.closest('.edit-task') && activePopup) {
            activePopup.remove();
            activePopup = null;
        }
    });

    // Close popup when clicking outside
    document.addEventListener('click', () => {
        if (activePopup) {
            activePopup.remove();
            activePopup = null;
        }
    });

    // Close popup on ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && activePopup) {
            activePopup.remove();
            activePopup = null;
        }
    });
});
