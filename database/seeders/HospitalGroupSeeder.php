<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class HospitalGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hospital_group = [
            ['id_group'=> 1,'id_hospital' => 1],
            ['id_group'=> 2,'id_hospital' => 2],
        ];

        DB::table('hospital_group')->insert($hospital_group);
    }
}
