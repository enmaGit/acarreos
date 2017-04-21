<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ubicacion_envio';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['latitud', 'longitud'];

    protected $hidden = ['created_at','updated_at'];

}
