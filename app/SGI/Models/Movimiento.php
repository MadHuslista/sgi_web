<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class Movimiento extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'movimientos';
    public $timestamps = false;

    protected $fillable = [
      'id_organizacion' ,
      'id_cuenta',
      'fecha' ,
      'nombre_movimiento',
      'nDocBanner' ,
      'tipo_doc' ,
      'rut' ,
      'num_doc',
      'identificador',
      'valor',
      'estado'
    ];

}
