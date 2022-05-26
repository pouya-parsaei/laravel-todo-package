<?php

namespace PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Requests\LabelStoreRequest;
use PouyaParsaei\LaravelToDo\Http\Resources\LabelResource;
use PouyaParsaei\LaravelToDo\Models\Label;

class LabelController extends Controller
{
    use  ResponseHelper;

    public function store(LabelStoreRequest $request)
    {
        $label = Label::create($request->all());
        return $this->respondCreated(trans('todo::messages.success'),$label->toArray());
    }

    public function index()
    {
        return LabelResource::collection(Label::all());
    }

}
