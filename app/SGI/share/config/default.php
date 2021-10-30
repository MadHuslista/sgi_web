<?php
/**
 * Configuraciones extras de la aplicación
 * @author Jorge Courbis
 * @since Sgi 1.0
 */

$config['db'] = array(
    'driver'   => 'mysql',
    'host'     => getenv('DBHOST'),
    'port'     => getenv('DBPORT'),
    'username'     => getenv('DBUSER'),
    'password' => getenv('DBPASS'),
    'database'     => getenv('DBSIS'),
    "charset"   => "utf8",
    'collation' => 'utf8_general_ci',
);


$config['secret'] = array('salt' => '%O5N+68E`|Ltg]z`L?)_NMt > `Gl2%yg?}q-V&X`VK+G1XBxASY(yHj2HX6`O`h');
