<?php

namespace PouyaParsaei\LaravelToDo\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use PouyaParsaei\LaravelToDo\Consts\TaskStatus;

class TaskToggleStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => ['required',Rule::in([TaskStatus::OPEN,TaskStatus::CLOSE])],
        ];
    }
}
