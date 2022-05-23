<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Models;

use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\Models\Task;
use Tests\TestCase;

class LabelTest extends TestCase
{
    public function testLabelRelationshipWithTask()
    {
        $count = rand(1, 10);
        $label = factory(Label::class)->create();

        $label->tasks()->createMany(
            factory(Task::class, $count)->make()->toArray()
        );

        $this->assertCount($count, $label->tasks);
        $this->assertInstanceOf(Task::class, $label->tasks->first());
    }
}
