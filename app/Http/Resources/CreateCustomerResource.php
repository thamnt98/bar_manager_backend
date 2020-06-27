<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateCustomerResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'furigana_name' => $this->furigana_name,
            'date_of_birth' => is_null($this->date_of_birth) ? $this->date_of_birth : Carbon::parse($this->date_of_birth)->format('Y-m-d')
        ];
    }
}
