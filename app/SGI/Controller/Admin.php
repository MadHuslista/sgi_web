<?php

namespace SGI\Controller;
use SGI\Lib\Auth;

use SGI\Models\Person;
use SGI\Models\Institution;
use SGI\Models\Role;
use SGI\Models\Log;
use Slim\Slim;
use Illuminate\Pagination\Paginator;

/**
* Esta clase gestiona la carga de datos
*
* @author Guillermo Briceño
* @since 0.1
*/

use Illuminate\Database\Capsule\Manager as DB;
class Admin extends \SlimController\SlimController
{

    public function cargaAgregarAlumnosAction()
    {
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

        $this->render('admin-cargaralumnos', array('rol' => $rol,'user_id' => $user_id, 'avatar' => $avatar,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario,));
    }
}
