<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstatusEnvio extends Model
{
    //
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'estatus_envio';

    public $timestamps = true;

    protected $hidden = ['created_at','updated_at'];

}
