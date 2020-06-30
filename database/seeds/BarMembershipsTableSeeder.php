<?php

use Illuminate\Database\Seeder;

class BarMembershipsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('bar_memberships')->insert([
            [
                'account_id' => '1',
                'bar_id' => '1',
                'role'=>'owner',
                'can_edit'=> '1',
                'created_at'=>now(),
                'updated_at'=>now()
            ],
        ]);
    }
}
