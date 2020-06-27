<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Customer extends JsonResource
{
    var $keepBottles;
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
            'furigana_name' => $this->furigana_name,
            'phone_number' => $this->phone_number,
            'icon' => is_null($this->icon) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $this->icon,
            'bottles' => $this->bottles,
            'company_name' => $this->company_name,
            'in_charge_cast' => $this->inChargeCast->name ?? null,
            'customer_total_income' => $this->customer_total_income,
            'last_arrival_time' => $this->last_arrival_time,
            'favorite_rank' => $this->favorite_rank,
            'income_rank' => $this->income_rank,
            'bar_name' => $this->bar_name,
            'keep_bottle_day_limit' => $this->keep_bottle_day_limit,
            'province' => $this->province,
            'district' => $this->district,
            'address' => $this->address,
            'note' => $this->note,
            'day_of_week_can_be_contact' => $this->day_of_week_can_be_contact,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'friends' => $this->friends,
        ];
    }
}
