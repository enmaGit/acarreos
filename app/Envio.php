<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['user_id', 'origen', 'destino', 'max_dias', 'valoracion', 'estatus_id'];

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

    public function toArray()
    {
        $array = parent::toArray();
        $array['ganador'] = $this->ganador();
        return $array;
    }

}
