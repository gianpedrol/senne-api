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
            ['name' => 'Hospital Samaritano'],
            ['name' => 'Hospital Unimed'],
            ['name' => 'Hospital Evangélico'],
        ];

        DB::table('hospitais')->insert($hospitais);
    }
}
