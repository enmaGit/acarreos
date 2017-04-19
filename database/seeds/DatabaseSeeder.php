<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call('TipoUserSeeder');
        $this->call('EstatusEnvioSeeder');
        $this->call('ProductoSeeder');
        $this->call('TransporteSeeder');
        $this->call('UserSeeder');
        $this->call('UserProductoSeeder');
        $this->call('EnvioSeeder');
    }
}
