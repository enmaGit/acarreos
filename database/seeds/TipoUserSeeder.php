<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


/**
 * Created by PhpStorm.
 * User: Eduim Serra
 * Date: 23/07/2015
 * Time: 05:47 PM
 */
class TipoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("tipo_usuario")->delete();

        DB::table("tipo_usuario")->insert(array(
            'descripcion' => 'Admin'
        ));

        DB::table("tipo_usuario")->insert(array(
            'descripcion' => 'Cliente'
        ));

        DB::table("tipo_usuario")->insert(array(
            'descripcion' => 'Transportista'
        ));
    }

}
