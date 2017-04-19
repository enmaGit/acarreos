<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    protected $table = 'comisiones';

    protected $fillable = ['producto_id', 'comision'];

    protected $hidden = ['created_at', 'updated_at'];

    public function tipoProducto()
    {
        return $this->belongsTo('App\TipoProducto', 'producto_id', 'id');
    }
}
