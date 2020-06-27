<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bar extends Model
{

    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'tel',  'is_trash','address'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'bar_memberships',  'bar_id', 'account_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customerSetting()
    {
        return $this->hasOne('App\Models\CustomerSetting', 'bar_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customers()
    {
        return $this->hasMany('App\Models\Customer', 'bar_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function bottles()
    {
        return $this->hasManyThrough('App\Models\Bottle', 'App\Models\BottleCategory',  'bar_id', 'category_id', 'id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function casts()
    {
        return $this->hasMany('App\Models\Cast', 'App\Models\bar_id',  'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->hasMany('App\Models\BottleCategory',  'bar_id', 'id');
    }

    public function job()
    {
        return $this->belongsTo(Job::class, 'job', 'name');
    }
}
