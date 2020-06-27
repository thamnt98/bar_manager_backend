<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class OrderHistoryResource extends JsonResource
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
            'bar_id' => $this->bar_id,
            'customer_id' => $this->customer_id,
            'bar_name' => $this->bar_name,
            'customer_name' => $this->customer_name,
            'customer_icon' => is_null($this->customer_icon) ? null : config('constant.amazon_web_service_domain') . config('constant.folder_avatar_customer_s3') . '/' . $this->customer_icon,
            'customer_furigana_name' => $this->customer_furigana_name,
            'type'=> $this->type,
            'in_charge_cast'=> $this->in_charge_cast,
            'arrival_time'=> $this->arrival_at,
            'leave_time' => $this->leave_at,
            'total_income' => $this->total_income,
            'stayed_time' => $this->stayed_time,
            'rank' => $this->rank,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'note' => $this->note,
            'is_trash'=> $this->is_trash,
            'staff' => User::find($this->staff_id) ?? null,
            'cast' => User::find($this->cast_id) ?? null,
            'pay_method' => $this->pay_method,
            'pay_day' => $this->pay_day,
            'debt' => $this->debt,
            'paid_money' => $this->paid_money ?? null,
            'remain_debt' => $this->remain_debt ?? null
        ];
    }
}
