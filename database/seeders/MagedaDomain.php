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
            ['codprocedencia' => 58, 'domains' => 'mageda.digital'],
            ['codprocedencia' => 58, 'domains' => 'senneliquor.com.br'],            
        ];

        DB::table('domains_hospitals')->insert($domain);
    }
}
