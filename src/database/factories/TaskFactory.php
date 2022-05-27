<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use PouyaParsaei\LaravelToDo\Consts\TaskStatus;
use PouyaParsaei\LaravelToDo\Models\Task;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'status' => Arr::random([TaskStatus::OPEN, TaskStatus::CLOSE]),
        'user_id' => factory(User::class)
    ];

});

$factory->state(Task::class, 'close', [
        'status' => TaskStatus::CLOSE,
    ]
);

$factory->state(Task::class, 'open', [
        'status' => TaskStatus::OPEN,
    ]
);

$factory->state(Task::class, 'withoutDefaultUserId', function (Faker $faker) {
    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'status' => Arr::random([TaskStatus::OPEN, TaskStatus::CLOSE]),
        'user_id' => null

    ];
    }
);

