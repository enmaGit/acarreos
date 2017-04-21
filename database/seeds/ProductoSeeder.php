<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table("productos")->insert(array(
            'nombre' => 'Mercancía',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Mudanza',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Vehículo / Moto',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Botes',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Vidrios/ Ventanas',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Cuidado especial',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Mascotas',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Alimentos y agricultura',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Carga consolidada (TLT)',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Caballos/ Ganado',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Otros',
            'descripcion' => 'ni idea',
            'comision' => '10',
            'dias_puja' => '3',
        ));
    }
}
