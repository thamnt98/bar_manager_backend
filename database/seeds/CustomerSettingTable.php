<?php

use Illuminate\Database\Seeder;

class CustomerSettingTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer_settings')->insert([
            [
                'order_by' => 'asc',
                'bar_id'=>'1',
                'keep_bottle_day_limit'=>'30',
                'order_name'=>'name',
                'created_at'=>now(),
                'updated_at'=>now()
            ],
        ]);
    }
}
