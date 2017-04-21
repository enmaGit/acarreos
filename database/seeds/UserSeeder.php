<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Created by PhpStorm.
 * User: Eduim Serra
 * Date: 23/07/2015
 * Time: 05:38 PM
 */
class UserSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        \DB::table("users")->delete();

        \DB::table("users")->insert(array(
            'tipo_user_id' => '1',
            'login' => 'acarreospanama',
            'password' => Hash::make('Adelle1989'),
            'nombre' => 'Christian',
            'apellido' => 'Cedeño',
            'telefono' => '77777777',
            'email' => 'christian.ernesto.c@gmail.com',
            'fecha_nac' => '1994-01-24'
        ));

        \DB::table("users")->insert(array(
            'tipo_user_id' => '1',
            'login' => 'enmanuel21498',
            'password' => Hash::make('123456789'),
            'nombre' => 'Enmanuel',
            'apellido' => 'Carrasquel',
            'telefono' => '04148704645',
            'email' => 'enmanuel21498@gmail.com',
            'fecha_nac' => '1994-01-24'
        ));

        factory(App\User::class, 60)->create([
            'tipo_user_id' => 2
        ])->each(function ($u) {
            $faker = Faker\Factory::create();
            $numRows = $faker->numberBetween(0, 5);
            for ($i = 0; $i < $numRows; $i++)
                    $u->envios()->save(factory(App\Envio::class)->make());
        });

        factory(App\User::class, 39)->create([
            'tipo_user_id' => 3
        ])->each(function ($u) {
            $faker = Faker\Factory::create();
            $numRows = $faker->numberBetween(0, 2);
            $seed = 1234;
            mt_srand($seed);
            for ($i = 0; $i < $numRows; $i++)
                $u->transportes()->save(factory(App\Transporte::class)->make());
        });

    }


}
