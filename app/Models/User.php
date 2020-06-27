<?php

namespace App\Models;

use App\Notifications\VerifyEmailRequest;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $table = "accounts";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'limit_plan_id', 'invite_code', 'invited_account', 'creator_id', 'email_verified_at', 'payjp_customer_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'requested_upgrade_plan_id' => 1
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bars()
    {
        return $this->belongsToMany('App\Models\Bar', 'bar_memberships',  'account_id', 'bar_id');
    }

    public function role()
    {
        return $this->bars()->first()->pivot->where('account_id', $this->id)->first()->role;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailRequest);
    }

    public function accountLimitPlan()
    {
        return $this->belongsTo(AccountLimitPlan::class, 'limit_plan_id', 'id');
    }
}
