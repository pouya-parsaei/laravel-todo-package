<?php

namespace PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Requests\RegisterRequest;

class LoginController extends Controller
{
    use  ResponseHelper;

    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
//            'api_token' => Str::random(60)
        ]);

        return $this->respondSuccess('You have been registered',$user->toArray());
    }

    public function loginForm()
    {
        return view('todo::auth.login');
    }

    public function login(Request $request)
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!auth()->attempt($attributes))
            return $this->respondNotAuthenticated('your credentials are invalid');

        $user = User::where('email',$attributes['email'])->first();
        $user->update([
            'api_token' => Str::random(60)
        ]);
        $token = User::where('id',$user->id)->first()->api_token;
        return $this->respondSuccess('You have Logged In Successfully',['token'=>$token]);
    }

}
