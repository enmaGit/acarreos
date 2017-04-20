<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoTransporte extends Model
{
    //
    protected $table = 'tipo_transporte';

    public $timestamps = true;

    protected $hidden = ['created_at','updated_at'];

}
