<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Envio;
use Illuminate\Support\Facades\Config;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $envios = Envio::where('estatus_id', Config::get('constants.ESTATUS_SUBASTA'))->orderBy('fecha_pub', 'desc')->get();
            foreach ($envios as $envio) {
                if ($envio->estaVencido()) {
                    $envio->estatus_id = Config::get('constants.ESTATUS_CANCELADO');
                    $envio->save();
                    $notification = new \App\Helpers\PushHandler;
                    $data = ['msg' => 'Su envio ha sido cancelado',
                        'tipo' => Config::get('constants.NOTIF_ENVIO_CANCELADO'),
                        'envio' => $envio->toJson()];
                    $ofertas = $envio->ofertas;

                    foreach ($ofertas as $oferta) {
                        $notification->generatePush($oferta->transportista, $data);
                    }
                    $notification->generatePush($envio->user, $data);
                }
            }
        })->dailyAt('01:00');
    }
}
