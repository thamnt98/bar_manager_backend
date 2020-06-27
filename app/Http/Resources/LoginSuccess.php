<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginSuccess extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $barIds = $this->user->bars()->get()->pluck('id')->toArray();
        $userData['id'] = $this->user->id;
        $userData['name'] = $this->user->name;
        $userData['email'] = $this->user->email;
        $userData['role'] = $this->user->role();
        $userData['verified'] = $this->user->hasVerifiedEmail();
        $userData['account_limit_plan'] = $this->user->accountLimitPlan->type;
        $userData['is_admin'] = $this->user->is_admin;
        $userData['bar_ids'] = $barIds;
        return [
            'token_type' => 'Bearer',
            'token' => $this->accessToken,
            'expires_in' => Carbon::parse($this->token->expires_at)->toDateTimeString(),
            'user' => $userData
        ];
    }
}
