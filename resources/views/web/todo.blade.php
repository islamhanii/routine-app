@extends('layouts.master')

@section('title')
    Routine App
@endsection

@section('css')
    <link href="{{ URL::asset('assets/css/todo.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div id="celebration-container"></div>

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
                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </form>

        <!-- Tasks List -->
        <ul class="list-group" id="todoList"></ul>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Delete Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this task?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteTask">
                        Delete
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
    {{-- Pass routes & csrf to JS --}}
    <script>
        window.todoConfig = {
            csrf: '{{ csrf_token() }}',
            routes: {
                byDate: '{{ route('tasks.byDate') }}',
                store: '{{ route('tasks.store') }}',
                toggle: '{{ route('tasks.toggle') }}',
                edit: '{{ route('tasks.edit') }}',
                reorder: '{{ route('tasks.reorder') }}',
                delete: '{{ route('tasks.delete') }}',
            },
            sounds: {
                check: '{{ URL::asset('assets/sounds/check.mp3') }}',
                uncheck: '{{ URL::asset('assets/sounds/uncheck.mp3') }}',
                celebrate: '{{ URL::asset('assets/sounds/celebrate.mp3') }}',
            }
        };
    </script>

    <script src="{{ URL::asset('assets/js/todo.js') }}"></script>
@endsection
