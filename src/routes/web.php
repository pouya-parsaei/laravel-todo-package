<?php

Route::group(['namespace' => 'PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1'], function () {
    Route::get('register', 'LoginController@registerForm')->name('register.form');
    Route::post('register', 'LoginController@register')->name('register');
    Route::get('login', 'LoginController@loginForm')->name('login.form');
    Route::post('login', 'LoginController@login')->name('login');
    Route::get('home', function () {
        return view('todo::home');
    })->name('home')->middleware('auth');
});
