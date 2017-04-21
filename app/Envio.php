<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Envio extends Model
{
    //
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'envios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'origen', 'destino', 'max_dias', 'valoracion', 'comentario', 'estatus_id', 'fecha_sug', 'hora_sug'];

    protected $hidden = ['created_at', 'updated_at'];

    public function estatus()
    {
        return $this->belongsTo('App\EstatusEnvio', 'estatus_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function productos()
    {
        return $this->hasMany('App\Producto', 'envio_id', 'id');
    }

    public function transportes()
    {
        return $this->belongsToMany('App\TipoTransporte', 'transporte_envio', 'envio_id', 'transporte_id');
    }

    public function ubicaciones()
    {
        return $this->hasMany('App\Ubicacion', 'envio_id', 'id')->orderBy('updated_at');
    }

    public function ofertas()
    {
        return $this->hasMany('App\Oferta', 'envio_id', 'id')->with('transportista');
    }

    public function ganador()
    {
        if ($this->ofertas()->where('ganador', 1)->first()) {
            return $this->ofertas()->with('transportista')->where('ganador', 1)->first();
        }
        return;
    }

    public function comisionFinal()
    {
        $maxComision = 0;
        foreach ($this->productos as $producto) {
            if ($producto->tipoProducto->comision > $maxComision) {
                $maxComision = $producto->tipoProducto->comision;
            }
        }
        return $maxComision;
    }

    public function diasPuja()
    {
        $maxDias = 2;
        foreach ($this->productos as $producto) {
            if ($producto->tipoProducto->dias_puja > $maxDias) {
                $maxDias = $producto->tipoProducto->dias_puja;
            }
        }
        return $maxDias;
    }

    public function estaVencido()
    {
        $fechaBorrar = Carbon::now()->subDays($this->diasPuja());
        $fechaPublicacion = Carbon::createFromFormat('Y-m-d', $this->fecha_pub);
        return $fechaBorrar->diffInDays($fechaPublicacion, false) < 0;

    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['ganador'] = $this->ganador();
        $array['comision_final'] = $this->comisionFinal();
        $array['dias_puja'] = $this->diasPuja();
        return $array;
    }

}
