<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bars')->insert([
            [
                'name' => 'Bar Test ',
                'tel'=>'0123-556-398',
                'address'=> 'số nhà 25 đường Nguyễn Khang',
                'created_at'=>now(),
                'updated_at'=>now()
            ],
        ]);
    }
}
