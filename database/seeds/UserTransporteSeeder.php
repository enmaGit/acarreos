<?php

use Illuminate\Database\Seeder;

class UserTransporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(App\User::class, 3)
            ->create()
            ->each(function($u) {
                $u->posts()->save(factory(App\Post::class)->make());
            });
    }
}
