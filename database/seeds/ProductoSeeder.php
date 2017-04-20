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

        DB::table("productos")->delete();
        //
        DB::table("productos")->insert(array(
            'nombre' => 'Artículos del hogar',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Mudanzas',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Veh�culos',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Botes',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Cuidado especial',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Mascotas',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Alimentos y agricultura',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Motos',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Maquinaria pesada',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Mercancías industriales y de negocio',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Carga consolidada (LTL)',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Caballos y ganado',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Cami�n con carga completa',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));

        DB::table("productos")->insert(array(
            'nombre' => 'Otros',
            'descripcion' => 'ni idea',
            'comision' => '15',
            'dias_puja' => '7',
        ));
    }
}
