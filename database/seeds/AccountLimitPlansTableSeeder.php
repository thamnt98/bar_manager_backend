<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountLimitPlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::table('account_limit_plans')->delete();
        // account_limit_plans
        DB::table('account_limit_plans')->insert([
            [
                'name' => 'Free',
                'type' => 'free',
                'customer_limit' => 100,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Limited (200)',
                'type' => 'limited',
                'customer_limit' => 200,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Limited(500)',
                'type' => 'limited',
                'customer_limit' => 500,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Unlimited',
                'type' => 'unlimited',
                'customer_limit' => null,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
