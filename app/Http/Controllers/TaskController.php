<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\DoneTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        return view('web.todo');
    }

    /**
     * Return tasks for a specific date as JSON
     */
    public function tasksByDate(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $tasks = Task::where('user_id', Auth::id())
            ->orderBy('priority')
            ->get()
            ->map(function ($task) use ($date) {
                $task->is_done = $task->doneTasks()
                    ->where('done_date', $date)
                    ->exists();
                return $task;
            });

        return response()->json($tasks);
    }

    /**
     * Store new task (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);

        $maxPriority = Task::where('user_id', Auth::id())->max('priority') ?? 0;

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'priority' => $maxPriority + 1,
        ]);

        return response()->json($task);
    }

    /**
     * Toggle task done per date
     */
    public function toggle(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $done = DoneTask::where('task_id', $request->task_id)
            ->where('done_date', $date)
            ->first();

        if ($done) {
            $done->delete();
        } else {
            DoneTask::create([
                'task_id' => $request->task_id,
                'done_date' => $date
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Reorder tasks (drag & drop)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array'
        ]);

        foreach ($request->order as $item) {
            Task::where('id', $item['id'])
                ->where('user_id', Auth::id())
                ->update(['priority' => $item['priority']]);
        }

        return response()->json(['success' => true]);
    }
}
