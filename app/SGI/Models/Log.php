<?php

namespace SGI\Models;
use Illuminate\Database\Capsule\Manager as DB;
class Log extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'logs';
    public $timestamps = false;
}
