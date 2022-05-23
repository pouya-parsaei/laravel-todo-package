<?php

namespace PouyaParsaei\LaravelToDo\Tests\Feature\Models;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PouyaParsaei\LaravelToDo\Models\Task;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testUserRelationshipWithTask()
    {
        $count = rand(1, 10);
        $user = factory(User::class)->create();

        $user->tasks()->createMany(
            factory(Task::class, $count)->make()->toArray()
        );
        $this->assertCount($count,$user->tasks);
        $this->assertInstanceOf(Task::class, $user->tasks->first());
    }


}
