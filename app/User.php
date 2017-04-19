<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Config;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['login', 'email', 'password', 'nombre', 'apellido', 'telefono', 'foto', 'fecha_nac', ''];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    public function isAnAdmin()
    {
        return ($this->tipo_user_id == Config::get('constants.TIPO_ADMIN'));
    }

    public function isAClient()
    {
        return ($this->tipo_user_id == Config::get('constants.TIPO_CLIENTE'));
    }

    public function isATransportist()
    {
        return ($this->tipo_user_id == Config::get('constants.TIPO_TRANSPORTISTA'));
    }

    public function tipoUsuario()
    {
        return $this->belongsTo('App\TipoUsuario', 'tipo_user_id', 'id');
    }

    public function transportes()
    {
        return $this->hasMany('App\Transporte', 'user_id', 'id');
    }

    public function productos()
    {
        return $this->belongsToMany('App\TipoProducto', 'carrier_producto', 'transpor_id', 'producto_id');
    }

    public function envios()
    {
        return $this->hasMany('App\Envio', 'user_id', 'id');
    }

    public function ofertas()
    {
        return $this->hasMany('App\Ofera', 'transpor_id', 'id');
    }

    public function puedeOfertar($idEnvio)
    {
        $productosUser = $this->productos()->lists('id');
        $productosEnvios = Envio::find($idEnvio)->productos()->lists('producto_id');
        $result = $productosEnvios->diff($productosUser);
        return $result->count() == 0;
    }

    public function getNombreCompletoAttribute()
    {
        return $this->nombre . " " . $this->apellido;
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->fecha_nac)->age;
    }

    public function getValoracionAttribute()
    {
        $sum = 0;
        $cont = 0;
        $ofertasGanadoras = Oferta::where('ganador', 1)->where('transpor_id', $this->id)->get();

        foreach ($ofertasGanadoras as $oferta) {
            if ($oferta->envio->valoracion != null) {
                $sum = $sum + $oferta->envio->valoracion;
                $cont++;
            }
        }
        if ($cont == 0) {
            return 0;
        }
        return ($sum / $cont);
    }

    public function toArray()
    {
        $array = parent::toArray();
        if ($this->isATransportist() || $this->isAnAdmin()) {
            $array['valoracion'] = $this->getValoracionAttribute();
        }
        return $array;
    }


}
