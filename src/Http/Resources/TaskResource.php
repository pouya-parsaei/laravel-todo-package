<?php

namespace PouyaParsaei\LaravelToDo\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'message' => trans('todo::messages.success'),
            'data' =>[
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            ]
        ];
    }

}
