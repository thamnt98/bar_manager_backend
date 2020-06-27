<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'bar_id',
      'name',
      'furigana_name',
      'icon',
      'company_name',
      'in_charge_cast_id',
      'age',
      'date_of_birth',
      'email',
      'phone_number',
      'line_account_id',
      'department',
      'post_no',
      'position',
      'job',
      'company_tower',
      'province',
      'district',
      'address',
      'note',
      'day_of_week_can_be_contact',
      'favorite_rank',
      'income_rank',
      'is_trash',
      'friends',
    ];

    public function orders()
    {
        return $this->belongsToMany('App\Models\OrderHistory', 'order_histories',  'customer_id');
    }

    public function inChargeCast()
    {
        return $this->belongsTo('App\Models\User', 'in_charge_cast_id');
    }

    public function keepBottles()
    {
        return $this->hasManyThrough('App\Models\KeepBottle', 'App\Models\OrderHistory');
    }
}
