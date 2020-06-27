<?php

namespace App\Models;

use App\Enums\OrderHistoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrderHistory extends Model
{
    use SoftDeletes;

    protected $table = "order_histories";

    protected $fillable = [
        'bar_id',
        'customer_id',
        'cast_id',
        'arrival_at',
        'leave_at',
        'total_income',
        'stayed_time',
        'type',
        'rank',
        'note',
        'is_trash',
        'staff_id',
        'pay_method',
        'pay_day',
        'debt'
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'rank' => 0
    ];

    public function bottles()
    {
        return $this->belongsToMany('App\Models\Bottle', 'bottles',  'order_id', 'bottle_id');
    }

    public static function subTimeAgo($month)
    {
        $date = Carbon::createFromFormat('m-Y',$month);
        $endTime = $date->lastOfMonth()->endOfDay()->toDateTimeString();
        $firstTime = $date->subMonth(11)->firstOfMonth()->toDateTimeString();
        return compact('firstTime', 'endTime');
    }

    public static function subTimeMonth($month)
    {
        $date = Carbon::createFromFormat('m-Y',$month);
        $endTime = $date->lastOfMonth()->endOfDay()->toDateTimeString();
        $firstTime = $date->firstOfMonth()->toDateTimeString();
        return compact('firstTime', 'endTime');
    }
}
