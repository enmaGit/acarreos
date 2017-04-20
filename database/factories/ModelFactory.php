<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Illuminate\Support\Facades\Hash;

$factory->define(App\User::class, function ($faker) {
    return [
        'nombre' => $faker->firstName,
        'apellido' => $faker->lastName,
        'login' => $faker->unique()->userName,
        'password' => Hash::make('123456789'),
        'telefono' => $faker->phoneNumber,
        'email' => $faker->email,
        'fecha_nac' => $faker->date(),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\Transporte::class, function ($faker) {
    $numRows = \App\TipoTransporte::all()->count();
    return [
        'transporte_id' => mt_rand(1, $numRows),
        'condicion' => $faker->text(100)
    ];
});

$factory->define(App\Envio::class, function ($faker) {
    $numRows = \App\EstatusEnvio::all()->count();
    return [
        'lat_origen' => $faker->latitude(),
        'lon_origen' => $faker->longitude(),
        'lat_destino' => $faker->latitude(),
        'lon_destino' => $faker->longitude(),
        'estatus_id' => $faker->numberBetween(1, $numRows),
        'max_dias' => $faker->numberBetween(5, 30),
        'fecha_pub' => $faker->date(),
        'fecha_res' => $faker->date(),
        'fecha_fin' => $faker->date(),
    ];
});

$factory->define(App\Producto::class, function ($faker) {
    $numRows = \App\TipoProducto::all()->count();
    return [
        'producto_id' => mt_rand(1, $numRows),
        'cantidad' => $faker->numberBetween(1, 10),
        'largo' => $faker->randomFloat(2, 0, 10),
        'ancho' => $faker->randomFloat(2, 0, 10),
        'alto' => $faker->randomFloat(2, 0, 10),
        'peso' => $faker->randomFloat(2, 0, 1000)
    ];
});
