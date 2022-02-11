<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BannerHome extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('banner')->insert(
            [
                "banner_home" =>  "",
            ]
        );
        
    }
}
