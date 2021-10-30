<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class PersonaCongreso extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'persona_has_congreso';
    public $timestamps = false;
}