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
<<<<<<< HEAD
                "image" =>  "",
=======
                "banner_home" =>  "",
>>>>>>> e45d571f8764261b9d5d5f06b988635eea5ee68e
            ]
        );
        
    }
}
