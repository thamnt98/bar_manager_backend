<?php

use Illuminate\Database\Seeder;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('accounts')->insert([
            [
                'name' => 'Admin',
                'email'=>'admin@gmail.com',
                'is_admin'=> 1,
                'password'=> bcrypt('password'),
                'email_verified_at' =>now(),
                'created_at'=>now(),
                'updated_at'=>now()
            ],
        ]);
    }
}
