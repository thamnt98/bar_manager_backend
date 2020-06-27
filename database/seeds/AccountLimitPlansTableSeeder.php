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
                'name' => 'フリープラン 無料 (100件登録可能)',
                'type' => 'free',
                'customer_limit' => 100,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'ベーシックプラン 980円/月  (200件登録可能)',
                'type' => 'limited',
                'customer_limit' => 200,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'スタンダードプラン 2980円/月  (500件登録可能)',
                'type' => 'limited',
                'customer_limit' => 500,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'プレミアムプラン 4980円/月  (無制限に登録可能)',
                'type' => 'unlimited',
                'customer_limit' => null,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
