@extends('layouts.master')

@section('title')
    Daily Tasks
@endsection

@section('css')
    <link href="{{ URL::asset('assets/css/todo.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="todo-card card-box shadow">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>🗓 Daily Tasks</h4>

            <!-- Date Picker -->
            <input type="date" id="taskDate" class="form-control w-auto"
                value="{{ request('date', now()->toDateString()) }}">
        </div>

        <!-- Add Task Form -->
        <form method="POST" action="{{ route('tasks.store') }}" class="mb-3">
            @csrf
            <input type="hidden" name="date" id="formDate">

            <div class="input-group">
                <input type="text" name="title" class="form-control" placeholder="New task..." required>
                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                </button>
            </div>
        </form>

        <!-- Tasks List -->
        <ul class="list-group" id="todoList">
            @foreach ($tasks as $task)
                <li class="list-group-item todo-item" data-id="{{ $task->id }}">

                    <div class="d-flex justify-content-between align-items-center">

                        <div class="form-check">
                            <input class="form-check-input toggle-done" type="checkbox" data-id="{{ $task->id }}"
                                {{ $task->is_done ? 'checked' : '' }}>

                            <span class="{{ $task->is_done ? 'done' : '' }}">
                                {{ $task->title }}
                            </span>
                        </div>

                        <i class="bi bi-grip-vertical drag-handle"></i>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endsection

@section('js')
    <script src="{{ URL::asset('assets/js/todo.js') }}"></script>
@endsection
