<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role(),
            'limit_plan_id' => $this->limit_plan_id,
            'verified' => $this->hasVerifiedEmail(),
            'bars' => $this->bars()->get(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'trashed' => $this->trashed()
        ];
    }
}
