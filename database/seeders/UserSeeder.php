<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(
            [
                'name' => 'Senne Liquor',
                'cpf' => '30188106006',
                'role_id' => '1',
                'status' => 1,
                'email' => 'dev@senne.com',
                'password' => bcrypt('654321'),
                'remember_token' => '',
            ]
        );
    }
}
