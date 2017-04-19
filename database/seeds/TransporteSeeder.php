<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'caja seca',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma baja',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma escalonada',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Solo cabeza tractora',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Contenedor refrigerado',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Furgoneta con neumáticos de conducción',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma multinivel',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Volquete',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma doble',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => '"Gooseneck" removible',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Plataforma extensible',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Tanque',
            'descripcion' => 'ni idea',
        ));

        DB::table("tipo_transporte")->insert(array(
            'nombre' => 'Otro',
            'descripcion' => 'ni idea',
        ));
    }
}
