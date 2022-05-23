<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Models;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\Models\Task;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function testTaskRelationshipWithUser()
    {
        $user = factory(User::class)->create();
        $task = Task::create([
            'title' => 'test',
            'description' => 'Lorem ipsum dolor',
            'status' => Arr::random([0, 1]),
            'user_id' => $user->id
        ]);

        $this->assertTrue(isset($task->user->id));
        $this->assertInstanceOf(User::class, $task->user);
    }

    public function testTaskRelationshipWithLabel()
    {
        $count = rand(1, 10);
        $task = factory(Task::class)->create();

        $task->labels()->createMany(
            factory(Label::class, $count)->make()->toArray()
        );

        $this->assertCount($count, $task->labels);
        $this->assertInstanceOf(Label::class, $task->labels->first());

    }
}
