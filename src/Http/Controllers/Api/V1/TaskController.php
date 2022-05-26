<?php

namespace PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use PouyaParsaei\LaravelToDo\Consts\TaskStatus;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Requests\TaskStoreRequest;
use PouyaParsaei\LaravelToDo\Http\Requests\TaskUpdateRequest;
use PouyaParsaei\LaravelToDo\Http\Resources\TaskResource;
use PouyaParsaei\LaravelToDo\Models\Task;
use PouyaParsaei\LaravelToDo\Notifications\TaskClosed;

class TaskController extends Controller
{
    use  ResponseHelper;

    public function store(TaskStoreRequest $request)
    {
        $task = $request->user()->tasks()->create($request->all());

        return $this->respondCreated(trans('todo::messages.success'), $task->toArray());
    }

    public function update(TaskUpdateRequest $request, Task $task)
    {
        if(!auth()->user()->hasThisTask($task))
            return  $this->respondNotAuthorized('todo::messages.errors.not authorized');

        $task->update($request->all());

        $taskAfterUpdate = Task::find($task->id);

        return $this->respondSuccess(trans('todo::messages.success'), $taskAfterUpdate->toArray());
    }


    public function openStatus(Task $task)
    {
        $task->update([
            'status' => TaskStatus::OPEN
        ]);

        $taskAfterUpdate = Task::find($task->id);

        return $this->respondSuccess(trans('todo::messages.success'), $taskAfterUpdate->toArray());
    }

    public function closeStatus(Task $task)
    {
        $task->update([
            'status' => TaskStatus::CLOSE
        ]);

        $taskAfterUpdate = Task::find($task->id);

        auth()->user()->notify(new TaskClosed($task));

        return $this->respondSuccess(trans('todo::messages.success'), $taskAfterUpdate->toArray());
    }

    public function show(Task $task)
    {
        if(!auth()->user()->hasThisTask($task))
            return  $this->respondNotAuthorized('todo::messages.errors.not authorized');

        return $this->respondSuccess(trans('todo::messages.success'), $task->toArray());

    }

    public function index()
    {
        $tasks = Task::where('user_id',auth()->user()->id)->with('labels')->get();
        return TaskResource::collection($tasks);
    }
}
