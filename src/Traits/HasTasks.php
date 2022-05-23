<?php

namespace PouyaParsaei\LaravelToDo\Traits;

use PouyaParsaei\LaravelToDo\Models\Task;

trait HasTasks
{
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
