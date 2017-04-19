<?php

/**
 * Created by PhpStorm.
 * User: Eduim Serra
 * Date: 25/07/2015
 * Time: 12:38 AM
 */

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstatusEnvioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("estatus_envio")->delete();

        DB::table("estatus_envio")->insert(array(
            'descripcion' => 'Subasta'
        ));

        DB::table("estatus_envio")->insert(array(
            'descripcion' => 'Cancelado'
        ));

        DB::table("estatus_envio")->insert(array(
            'descripcion' => 'Desarrollo'
        ));

        DB::table("estatus_envio")->insert(array(
            'descripcion' => 'Finalizado'
        ));
    }
}
