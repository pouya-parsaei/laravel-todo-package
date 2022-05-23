<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use PouyaParsaei\LaravelToDo\Models\Label;

$factory->define(Label::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->word,
    ];
});
