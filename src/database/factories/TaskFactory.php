<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;
use PouyaParsaei\LaravelToDo\Models\Task;

$factory->define(Task::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'description' => $faker->sentence,
        'status' => Arr::random([0,1]),
        'user_id' => factory(User::class)
    ];
});
