<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BarsTableSeeder::class,
            AccountsTableSeeder::class,
            BarMembershipsTableSeeder::class,
            CustomerSettingTable::class,
            AccountLimitPlansTableSeeder::class,
        ]);
    }
}
