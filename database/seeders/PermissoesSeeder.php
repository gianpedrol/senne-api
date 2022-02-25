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
            ['descricao' => 'Administrador','nivel' => 1],
            ['descricao' => 'Agendamento','nivel' => 2],
            ['descricao' => 'Resultados','nivel' => 3],
        ];

        DB::table('permissoes')->insert($permissao);
    }
}
