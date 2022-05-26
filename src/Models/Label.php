<?php

namespace PouyaParsaei\LaravelToDo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = ['name'];

    public function tasks()
    {
        return $this->belongsToMany(Task::class);
    }

    public function getCountAuthUserTasksAttribute()
    {
        return $this->loadCount([ 'tasks' => function (Builder $query) {
            $query->where('user_id', auth()->user()->id);
        }])->tasks_count;
    }
}
