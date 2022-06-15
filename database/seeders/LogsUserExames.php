<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class LogsUserExames extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $logs = [
            ['log_description' => 'Usuário Criou um Grupo', 'id' => 6],
            ['log_description' => 'Usuário Editou um Grupo', 'id' => 7],

            ['log_description' => 'Acessou um Laudo Principal', 'id' => 8],
            ['log_description' => 'Acessou um Atendimento', 'id' => 9],
            ['log_description' => 'Acessou Lista Atendimentos', 'id' => 10],
            ['log_description' => 'Aprovou um Usuário', 'id' => 11],
            ['log_description' => 'Inativou um Usuário', 'id' => 12]

        ];

        DB::table('logs_action')->insert($logs);
    }
}
