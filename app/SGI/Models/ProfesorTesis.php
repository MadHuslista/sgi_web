<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class ProfesorTesis extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'profesor_has_tesis';
    public $timestamps = false;
}