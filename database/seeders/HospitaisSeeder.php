<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class HospitaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hospitais = [
            ['id'=> 1, 'name' => 'Hospital Samaritano'],
            ['id'=> 2, 'name' => 'Hospital Unimed'],
        ];

        DB::table('hospitais')->insert($hospitais);
    }
}
