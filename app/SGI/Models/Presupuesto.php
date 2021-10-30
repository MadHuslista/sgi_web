<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class Presupuesto extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'presupuestos';

  
    public $timestamps = false;

    protected $fillable = ['anio','id_organizacion','cod_cuenta',
          'ppto_enero','ppto_febrero','ppto_marzo','ppto_abril',
          'ppto_mayo','ppto_junio','ppto_julio','ppto_agosto',
          'ppto_septiembre','ppto_octubre','ppto_noviembre',
          'ppto_diciembre'
    ];

}
