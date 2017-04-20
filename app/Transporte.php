<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transporte extends Model
{
    protected $table = 'user_transporte';

    public $timestamps = true;

    protected $hidden = ['created_at','updated_at'];

    protected $fillable = ['condicion'];
    //

    public function tipoTransporte()
    {
        return $this->belongsTo('App\TipoTransporte','transporte_id','id');
    }

}
