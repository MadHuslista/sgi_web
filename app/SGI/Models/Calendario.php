<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class Calendario extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'calendario';
    public $timestamps = false;

	public static function insertarFecha($fecha_mostrar, $id_calendario){
		$query = "UPDATE calendario SET fecha_mostrar = '".$fecha_mostrar."' WHERE id = ". $id_calendario;
		return DB::update(DB::raw($query));
	}

}