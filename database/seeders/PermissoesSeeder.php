<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class PermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

         $permissao = [
            ['descricao' => 'Agendamento','nivel' => 1],
            ['descricao' => 'Resultados','nivel' => 2],
        ];

        DB::table('permissoes')->insert($permissao);
    }
}
