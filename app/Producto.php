<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    //
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productos_envio';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['cantidad', 'largo', 'ancho', 'alto', 'peso', 'producto_id'];

    protected $hidden = ['created_at','updated_at'];

    public function tipoProducto()
    {
        return $this->belongsTo('App\TipoProducto', 'producto_id', 'id');
    }

    public function envio()
    {
        return $this->belongsTo('App\Envio', 'envio_id', 'id');
    }

}
