<?php

use App\Envio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnvioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $envios = Envio::all();
        DB::table('transporte_envio')->delete();
        DB::table('productos_envio')->delete();
        $faker = Faker\Factory::create();
        $seed = 1234;
        $totalTipoTransportes = 14;
        foreach ($envios as $envio) {
            $numRows = $faker->randomNumber(1);
            mt_srand($seed++);
            for ($i = 0; $i < $numRows; $i++)
                $envio->productos()->save(factory(App\Producto::class)->make());
            for ($i = 0; $i < 3; $i++) {
                $idTipoTrans = $i + 1;
                $envio->transportes()->save(App\TipoTransporte::find($idTipoTrans));
            }
        }
    }
}
