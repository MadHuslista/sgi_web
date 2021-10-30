<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;

class Person extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'persona';
    public $timestamps = false;

}