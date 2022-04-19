<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class LogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $logs = [
            ['log_description' => 'Usuário logou', 'id_log' => 1],
            ['log_description' => 'Usuário deslogou', 'id_log' => 2],
            ['log_description' => 'Usuário editou um usuário', 'id_log' => 3],
            ['log_description' => 'Usuário criou um usuário', 'id_log' => 4],
            ['log_description' => 'Usuário deletou um usuário', 'id_log' => 5],

        ];

        DB::table('logs_action')->insert($logs);
    }
}
