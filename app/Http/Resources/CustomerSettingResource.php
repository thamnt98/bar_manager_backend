<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }
        return [
            'bar_id' => $this->bar_id,
            'order_name' => $this->order_name,
            'order_by' => $this->order_by,
            'record_per_visit_page' => $this->record_per_visit_page,
            'record_per_customer_page' => $this->record_per_customer_page,
            'keep_bottle_day_limit' => $this->keep_bottle_day_limit
        ];
    }
}
