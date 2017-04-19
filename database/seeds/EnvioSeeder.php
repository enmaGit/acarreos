<?php

use App\Envio;
use Illuminate\Database\Seeder;

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
        DB::table('productos_envio')->truncate();
        $faker = Faker\Factory::create();
        $seed = 1234;
        foreach ($envios as $envio) {
            $numRows = $faker->randomNumber(1);
            mt_srand($seed++);
            for ($i = 0; $i < $numRows; $i++)
                $envio->productos()->save(factory(App\Producto::class)->make());
        }
    }
}
