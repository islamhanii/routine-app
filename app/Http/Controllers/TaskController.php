<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Tasks\AddTaskRequest;
use App\Http\Requests\Tasks\DeleteTaskRequest;
use App\Http\Requests\Tasks\ReorderTaskRequest;
use App\Http\Requests\Tasks\ToggleTaskRequest;
use App\Http\Requests\Tasks\UpdateTaskRequest;
use App\Models\Task;
use App\Models\DoneTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return view('web.todo');
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function tasksByDate(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $tasks = Task::select(
            'tasks.id',
            'tasks.title',
            'tasks.priority',
            DB::raw('CASE WHEN done_tasks.task_id IS NOT NULL THEN 1 ELSE 0 END AS done')
        )
            ->leftJoin('done_tasks', function ($join) use ($date) {
                $join->on('tasks.id', '=', 'done_tasks.task_id')
                    ->where('done_tasks.done_date', $date);
            })
            ->where('user_id', Auth::id())
            ->orderBy('priority')
            ->get();

        return $this->apiResponse(200, 'tasks', null, $tasks);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function store(AddTaskRequest $request)
    {
        $maxPriority = Task::where('user_id', Auth::id())->max('priority') ?? 0;

        $task = Task::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'priority' => $maxPriority + 1
        ]);

        return $this->apiResponse(201, __('messages.task_created'), null, $task);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function edit(UpdateTaskRequest $request)
    {
        $task = Task::findOrFail($request->task_id);

        $task->update([
            'title' => $request->title
        ]);

        return $this->apiResponse(200, __('messages.task_updated'), null, $task);
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function toggle(ToggleTaskRequest $request)
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

        return $this->apiResponse(200, __('messages.task_toggled'));
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function reorder(ReorderTaskRequest $request)
    {
        $request->validate([
            'order' => 'required|array'
        ]);

        try {
            DB::beginTransaction();
            foreach ($request->order as $item) {
                Task::where('id', $item['id'])
                    ->where('user_id', Auth::id())
                    ->update(['priority' => $item['priority']]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->apiResponse(500, __('messages.error_occured'));
        }

        return $this->apiResponse(200, __('messages.tasks_reordered'));
    }

    /*-----------------------------------------------------------------------------------------------*/

    public function delete(DeleteTaskRequest $request)
    {
        $task = Task::findOrFail($request->task_id);
        $task->delete();

        return $this->apiResponse(200, __('messages.task_deleted'));
    }
}
