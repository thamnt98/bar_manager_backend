<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BottleCategory extends Model
{
    protected $table = "bottle_categories";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'serial', 'is_trash', 'display_name', 'bar_id'
    ];

    public function bar()
    {
        return $this->belongsTo('App\Models\Bar', 'bar_id',  'id');
    }
}
