# Laravel To-Do Package
___
## Laravel To-Do package with authentication
[![GitHub issues](https://img.shields.io/github/issues/pouya-parsaei/laravel-todo-package)](https://github.com/pouya-parsaei/laravel-todo-package/issues)
[![GitHub forks](https://img.shields.io/github/forks/pouya-parsaei/laravel-todo-package)](https://github.com/pouya-parsaei/laravel-todo-package/network)
[![GitHub stars](https://img.shields.io/github/stars/pouya-parsaei/laravel-todo-package)](https://github.com/pouya-parsaei/laravel-todo-package/stargazers)
___
### Installation
* Install package with composer:<br>
``composer require pouya-parsaei/laravel-to-do``
<br><br>
* To authenticate user If `User` Model has `$fillable` property, you must add `api_token` to this array:<br>
``
protected $fillable = ['name', 'email', 'password','api_token'];
``
  <br><br>
* add `HasTasks` and `HasToken` traits to `User` Model:<br>
```
<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PouyaParsaei\LaravelToDo\Traits\HasTasks;
use PouyaParsaei\LaravelToDo\Traits\HasToken;

class User extends Authenticatable
{
    use Notifiable, HasTasks, HasToken;

}
```

* set your `MAIL` settings in `.env` file
  <br><br>
* set your `QUEUE_CONNECTION` in `.env` file
####note:
* If you want to use `database` as `QUEUE_CONNECTION` and  already don’t have  jobs table in database, create it:<br>
``php artisan queue:table``
<br><br>
* If you already do not have `notifications` table in the database, create it:<br>
``php artisan notification:table``
  <br><br>
* In `app/Exceptions/Handler.php` use `ResponseHelper` Trait and edit `render` function like this:
```
<?php

namespace App\Exceptions;


use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException as IlluminateModelNotFoundException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseHelper;

    public function render($request, Throwable $exception)
    {
        if (($exception instanceof NotFoundHttpException || $exception instanceof IlluminateModelNotFoundException) && $request->wantsJson()) {
            return $this->respondNotFound(trans('todo::messages.errors.not found'));
        }
        return parent::render($request, $exception);
    }
}
```

* run migrate:<br>
  ``php artisan migrate``
  <br><br>
* start worker:<br>
``php artisan queue:work``
___

