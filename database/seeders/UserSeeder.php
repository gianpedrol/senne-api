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
        $users = [
            [
                'id' => 1,
                'name' => 'Senne Liquor',
                'cpf' => '30188106006',
                'role_id' => '1',
                'status' => 1,
                'email' => 'dev@senne.com',
                'password' => bcrypt('654321'),
                'remember_token' => '',
            ],
            [
                'id' => 2,
                'name' => 'Administrador Grupo Unimed',
                'cpf' => '30188106006',
                'role_id' => '2',
                'status' => 1,
                'email' => 'dev@unimed.com.br',
                'password' => bcrypt('654321'),
                'remember_token' => '',
            ],
            [
                'id' => 3,
                'name' => 'Administrador Grupo Samaritano',
                'cpf' => '30188106006',
                'role_id' => '2',
                'status' => 1,
                'email' => 'dev@samaritano.com.br',
                'password' => bcrypt('654321'),
                'remember_token' => '',
            ]
            
        ];

        DB::table('users')->insert($users);

       
    }
}
