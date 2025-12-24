@extends('layouts.master')

@section('title')
    Routine App
@endsection

@section('css')
    <link href="{{ URL::asset('assets/css/todo.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="todo-card card-box shadow">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🗓 Daily Tasks</h4>
            <input type="date" id="taskDate" class="form-control w-auto">
        </div>

        <!-- Add Task Form -->
        <form id="addTaskForm" class="mb-3">
            @csrf
            <div class="input-group">
                <input type="text" id="taskTitle" class="form-control" placeholder="New task..." required>
                <button class="btn btn-primary"><i class="bi bi-plus-lg"></i></button>
            </div>
        </form>

        <!-- Tasks List -->
        <ul class="list-group" id="todoList"></ul>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const todoList = document.getElementById('todoList');
            const dateInput = document.getElementById('taskDate');
            const taskTitle = document.getElementById('taskTitle');

            // Helper: Load tasks for a date
            function loadTasks(date) {
                fetch(`{{ route('tasks.byDate') }}?date=${date}`)
                    .then(res => res.json())
                    .then(tasks => {
                        todoList.innerHTML = '';
                        tasks.forEach(task => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item todo-item';
                            li.dataset.id = task.id;
                            li.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input toggle-done" type="checkbox" data-id="${task.id}" ${task.is_done ? 'checked' : ''}>
                                <span class="${task.is_done ? 'done' : ''}">${task.title}</span>
                            </div>
                            <i class="bi bi-grip-vertical drag-handle"></i>
                        </div>
                    `;
                            todoList.appendChild(li);
                        });
                    });
            }

            // Initial load: today
            const today = new Date().toISOString().substr(0, 10);
            dateInput.value = today;
            loadTasks(today);

            // On date change
            dateInput.addEventListener('change', function() {
                loadTasks(this.value);
            });

            // Add Task
            document.getElementById('addTaskForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const date = dateInput.value;

                fetch('{{ route('tasks.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        title: taskTitle.value
                    })
                }).then(() => {
                    taskTitle.value = '';
                    loadTasks(date);
                });
            });

            // Delegate toggle-done event
            todoList.addEventListener('change', function(e) {
                if (e.target && e.target.classList.contains('toggle-done')) {
                    const taskId = e.target.dataset.id;
                    const date = dateInput.value;

                    fetch(`{{ route('tasks.toggle') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify({
                            task_id: taskId,
                            date: date
                        })
                    }).then(() => loadTasks(date));
                }
            });

            // Drag & Drop Priority
            new Sortable(todoList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function() {
                    const order = Array.from(todoList.querySelectorAll('.todo-item')).map((el, index) =>
                        ({
                            id: el.dataset.id,
                            priority: index + 1
                        }));

                    fetch('{{ route('tasks.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.csrfToken
                        },
                        body: JSON.stringify({
                            order
                        })
                    });
                }
            });

        });
    </script>
@endsection
