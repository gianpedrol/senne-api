<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert(
            [
                'role_id' => '1',
                'name' => "Admin APP",
                'email' => "admin@admin.com",
                'status' => 1,
                'password' => Hash::make('12345678')
            ]
        );
    }
}
