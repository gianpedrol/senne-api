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
                'name' => 'Gabriela ',
                'cpf' => '',
                'role_id' => '1',
                'status' => 1,
                'email' => 'gabriela.senne@senneliquor.com.br',
                'password' => bcrypt('%&yAXNF'),
                'remember_token' => '',
            ],
            [
                'id' => 3,
                'name' => 'Marlene Aparecida Ferreira ',
                'cpf' => '',
                'role_id' => 3,
                'status' => 1,
                'email' => 'marlene@teste.com',
                'password' => bcrypt('654321'),
                'cod_pf' => 'E2C2F72E90ED4552E053E600A8C0FE22',
                'remember_token' => '',
            ]
                
        ];

        DB::table('users')->insert($users);

       
    }
}
