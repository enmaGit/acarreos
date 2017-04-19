<?php

use App\TipoProducto;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = User::where('tipo_user_id',3)->get();
        DB::table('carrier_producto')->truncate();
        $faker = Faker\Factory::create();
        $seed = 1234;
        foreach ($users as $user) {
            $numRows = $faker->randomNumber(1);
            $numTipoProductos = TipoProducto::all()->count();
            mt_srand($seed++);
            for ($i = 0; $i < $numRows; $i++) {
                $tipoProductoId = $faker->numberBetween(1, $numTipoProductos);
                $user->productos()->attach($tipoProductoId);
            }
        }
    }
}
