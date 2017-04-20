<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    //

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ofertas';

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['precio_puja', 'dias', 'hora_salida', 'fecha_salida'];

    protected $hidden = ['created_at', 'updated_at'];

    public function transportista()
    {
        return $this->belongsTo('App\User', 'transpor_id', 'id');
    }

    public function envio()
    {
        return $this->belongsTo('App\Envio', 'envio_id', 'id');
    }

}
