<?php

Route::group([
    'namespace' => 'PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1',
    'middleware' => ['api'],
    'prefix' => 'todo/api/v1'], function () {
    Route::get('auth', 'LoginController@loginForm')->name('todo.auth.form');
});

Route::group([
    'namespace' => 'PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1',
    'middleware' => ['api'],
    'prefix' => 'todo/api/v1'], function () {

    Route::post('register', 'LoginController@register')->name('todo.register');
    Route::post('login', 'LoginController@login')->name('todo.login');


    Route::middleware('auth:api')->group(function () {
        Route::get('home', 'HomeController@index')->name('todo.home');
        Route::prefix('labels')->name('labels.')->group(function () {
            Route::post('', 'LabelController@store')->name('store');
            Route::get('', 'LabelController@index')->name('index');
        });

        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::post('', 'TaskController@store')->name('store');
            Route::put('{task}', 'TaskController@update')->name('update');
            Route::put('{task}/open-status', 'TaskController@openStatus')->name('open-status');
            Route::put('{task}/close-status', 'TaskController@closeStatus')->name('close-status');
            Route::post('{task}/labels', 'LabelTaskController@addLabelsToTask')->name('add-label');
            Route::get('', 'TaskController@index')->name('index');
            Route::get('{task}', 'TaskController@show')->name('show');
        });
    });
});
