<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transporte extends Model
{
    protected $table = 'user_transporte';

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = ['condicion', 'placa', 'poliza_compa', 'poliza_numero'];

    //

    public function tipoTransporte()
    {
        return $this->belongsTo('App\TipoTransporte', 'transporte_id', 'id');
    }

}
