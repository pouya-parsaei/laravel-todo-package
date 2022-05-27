<?php

namespace PouyaParsaei\LaravelToDo\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class LabelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
                'id' => $this->id,
                'label' => $this->name,
                'total_tasks_having_this_label' => $this->count_auth_user_tasks
        ];
    }
}
