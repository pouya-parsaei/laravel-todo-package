<?php


Route::group(['namespace' => 'PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1', 'middleware' => ['api', 'auth:api'],
    'prefix' => 'todo/api/v1'], function () {

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




//Route::put('api-token/generate', 'PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1\ApiTokenController@update')
//    ->middleware(['api', 'apiAuth'])
//    ->name('token.generate');
//
//
//Route::get('test', function () {
//    $label = Label::create(['name' => 'test' . rand(10, 30)]);
//    $user = User::create([
//        'name' => 'test' . rand(10, 30),
//        'email' => 'test' . rand(10, 30),
//        'email_verified_at' => now(),
//        'api_token' => Str::random(60),
//        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//        'remember_token' => Str::random(10)
//
//    ]);
//    foreach (range(1, 3) as $item) {
//        $task = Task::create([
//            'user_id' => $user->id,
//            'title' => 'test' . rand(1, 5),
//            'description' => 'test' . rand(2, 6)
//        ]);
//        $label->tasks()->attach($task);
//    }
//    $labels = Label::withCount(['tasks' => function (Builder $query) {
//        $query->where('user_id', 27);
//    }])->get();
//    dd($labels);
//});
//
//Route::get('notify', function () {
//    $user = User::create([
//        'name' => 'test' . rand(10, 30),
//        'email' => 'test@gmail.com',
//        'email_verified_at' => now(),
//        'api_token' => Str::random(60),
//        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
//        'remember_token' => Str::random(10)
//
//    ]);
//    Task::create([
//        'user_id' => $user->id,
//        'title' => 'test' . rand(1, 5),
//        'description' => 'test' . rand(2, 6)
//    ]);
//
//    $user = User::first();
//    $video = Task::first();
//    $user->notify(new TaskClosed($video));
//});
