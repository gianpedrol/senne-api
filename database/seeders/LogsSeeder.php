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
            ['log_description' => 'Usuário logou', 'id' => 1],
            ['log_description' => 'Usuário deslogou', 'id' => 2],
            ['log_description' => 'Usuário editou um usuário', 'id' => 3],
            ['log_description' => 'Usuário criou um usuário', 'id' => 4],
            ['log_description' => 'Usuário deletou um usuário', 'id' => 5],

            ['log_description' => 'Usuário Criou um Grupo', 'id' => 6],
            ['log_description' => 'Usuário Editou um Grupo', 'id' => 7],

            ['log_description' => 'Acessou um Laudo Principal', 'id' => 8],
            ['log_description' => 'Acessou um Atendimento', 'id' => 9],
            ['log_description' => 'Acessou Lista Atendimentos', 'id' => 10],

        ];

        DB::table('logs_action')->insert($logs);
    }
}
