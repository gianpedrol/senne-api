<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class LogsResultsExams extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $logs = [
            ['log_description' => 'Criou uma observação ', 'id' => 13],
            ['log_description' => 'Imprimiu um protocolo ', 'id' => 14],
            ['log_description' => 'Solicitou Acréscimo de exame ', 'id' => 15],
        ];

        DB::table('logs_action')->insert($logs);
    }
}
