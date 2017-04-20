<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = 'tipo_usuario';

    public $timestamps = true;

    protected $hidden = ['created_at','updated_at'];


}
