<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class MagedaDomain extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $domain = [
            ['codprocedencia' => 7, 'domains' => 'mageda.digital'],
        ];

        DB::table('domains_hospitals')->insert($domain);
    }
}
