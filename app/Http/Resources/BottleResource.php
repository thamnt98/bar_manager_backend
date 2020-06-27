<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BottleResource extends JsonResource
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
            'bar_id' => $this->bar_id,
            'bar_name' => $this->bar_name,
            'category_id' => $this->category_id,
            'serial' => $this->serial,
            'is_trash'=> $this->is_trash,
        ];
        if(!$this->category()){
            $arrayData['category_name'] = $this->category_name;
        } else {
            $arrayData['category_name'] = $this->category()->first()->name;
        }
        return $arrayData;
    }
}
