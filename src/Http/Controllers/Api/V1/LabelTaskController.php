<?php

namespace PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Requests\AddLabelsToTaskRequest;
use PouyaParsaei\LaravelToDo\Models\Task;


class LabelTaskController extends Controller
{
    use  ResponseHelper;

    public function addLabelsToTask(AddLabelsToTaskRequest $request, Task $task)
    {
        if (!auth()->user()->hasThisTask($task))
            return $this->respondNotAuthorized(trans('todo::messages.errors.not authorized'));

        $task->labels()->syncWithoutDetaching($request->labels);

        $taskAfterUpdateWithLabels = Task::with('labels:id,name')->find($task->id)->toArray();

        return $this->respondCreated(trans('todo::messages.success'), $taskAfterUpdateWithLabels);
    }
}
