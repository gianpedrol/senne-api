<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            ['id' => 1, 'description_department' => 'Corpo Clínico'],
            ['id' => 2, 'description_department' => 'Enfermagem'],
            ['id' => 3, 'description_department' => 'Administrativo'],
        ];

        DB::table('departments')->insert($departments);
    }
}
