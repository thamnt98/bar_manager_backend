<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BottleCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $arrayData = [
            'id' => $this->id,
            'name' => $this->name,
            'serial' => $this->serial,
            'bar_id' => $this->bar_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_trash'=> $this->is_trash,
        ];

        if(!$this->bar()){
            $arrayData['bar_name'] = $this->bar_name;
        } else {
            $arrayData['bar_name'] = $this->bar()->first()->name;
        }
        return $arrayData;
    }
}
