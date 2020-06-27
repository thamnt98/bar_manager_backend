<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bottle extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'serial', 'is_trash', 'code', 'category_id'
    ];

    public function orders()
    {
        return $this->belongsToMany('App\Models\OrderHistory', 'order_histories',  'bottle_id', 'order_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\BottleCategory', 'category_id',  'id');
    }
}
