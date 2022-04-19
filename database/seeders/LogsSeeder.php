<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            ['log' => 'Usuário logou'],
            ['log' => 'Usuário deslogou'],
            ['log' => 'Usuário editou um usuário'],
            ['log' => 'Usuário criou um usuário'],
            ['log' => 'Usuário deletou um usuário'],

        ];

        DB::table('logs_action')->insert($logs);
    }
}
