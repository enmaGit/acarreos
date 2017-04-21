<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    //
    protected $table = 'notificaciones';
    
    protected $hidden = ['created_at', 'updated_at'];
}
