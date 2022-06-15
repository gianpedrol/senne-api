<?php

namespace Database\Seeders;

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
        $this->call(UserSeeder::class);
        $this->call(PermissoesSeeder::class);
        $this->call(LogsSeeder::class);
        $this->call(LogsUserExames::class);
        $this->call(DepartmentSeeder::class);
        $this->call(DomainsSeeder::class);
    }
}
