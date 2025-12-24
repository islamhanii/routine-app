document.addEventListener('DOMContentLoaded', function () {

    const dateInput = document.getElementById('taskDate');
    const formDate = document.getElementById('formDate');

    // Sync form date with selected date
    formDate.value = dateInput.value;

    dateInput.addEventListener('change', function () {
        window.location.href = `?date=${this.value}`;
    });

    // Toggle done
    document.querySelectorAll('.toggle-done').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            fetch(`/tasks/${this.dataset.id}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
        });
    });

    // Drag & Drop Priority
    new Sortable(todoList, {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function () {
            let order = [];
            document.querySelectorAll('.todo-item').forEach((el, index) => {
                order.push({
                    id: el.dataset.id,
                    priority: index + 1
                });
            });

            fetch('/tasks/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({ order })
            });
        }
    });
});
