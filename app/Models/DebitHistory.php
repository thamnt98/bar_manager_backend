<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebitHistory extends Model
{

    protected $table = "debit_histories";

    protected $fillable = [
        'order_id',
        'pay_day',
        'paid_money',
    ];
}
