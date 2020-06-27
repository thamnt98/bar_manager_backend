<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'label_order_setting', 'record_per_visit_page', 'record_per_customer_page', 'bar_id', 'keep_bottle_day_limit', 'order_name', 'order_by'
    ];
}
