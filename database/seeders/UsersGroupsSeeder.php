<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class UsersGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users_hospitals = [
            ['id' => 1, 'id_user'=> 2,'id_group' => 2, 'id_permissao' => 1],
            ['id' => 2, 'id_user'=> 3,'id_group' => 1, 'id_permissao' => 1],
            ['id' => 3, 'id_user'=> 4,'id_group' => 1, 'id_permissao' => 3],

        ];

        DB::table('users_groups')->insert($users_hospitals);
    }
}
