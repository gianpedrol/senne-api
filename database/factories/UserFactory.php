<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name' => 'Senne Liquor',
        'role_id' => '1',
        'image' => NULL,
        'status' => 1,
        'username' => 'admin',
        'email' => 'dev@senne.com',
        'password' => bcrypt('123456'),
        'remember_token' => '',
    ];
});
