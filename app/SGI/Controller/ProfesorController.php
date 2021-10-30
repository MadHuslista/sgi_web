<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;

use SGI\Models\Profesor;
use SGI\Models\Titulo;
use SGI\Models\Plan;
use SGI\Models\Proyecto;
use SGI\Models\Publicacion;

use PHPExcel;

use Illuminate\Database\Capsule\Manager as DB;

class ProfesorController extends \SlimController\SlimController
{

    protected $app;
    function __construct() {
        $this->app = Slim::getInstance();
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function profesorPerfilAction($id){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();


      $profesor=Profesor::where('id',$id)->first();
      $titulo=Titulo::where('profesor_id',$id)->get();

      $plan=Plan::join('plan_has_profesor', 'plan.id', '=', 'plan_has_profesor.plan_id')
      ->where('plan_has_profesor.profesor_id',$id)
      ->get();

      $total_pub=Publicacion::where('profesor_id',$id)->count();
      $cant_proyectos=Proyecto::where('profesor_id',$id)->count();
      
      $publicaciones=Publicacion::where('profesor_id',$id)->get();
      $proyectos=Proyecto::join('linea_investigativa', 'linea_investigativa.id', '=', 'proyecto.linea_investigativa_id')
      ->where('profesor_id',$id)
      ->get(['proyecto.nombre','linea_investigativa.nombre as linea_investigativa','proyecto.anio_inicio']);

      $this->render('profesor-perfil',
        array(
          'rol' => $rol,
          'user_id' => $user_id,
          'nombre_completo' => $nombre_completo, 
          'nombre_usuario' => $nombre_usuario, 
          'avatar' => $avatar,
          'profesor'=>$profesor,
          'titulos'=>$titulo,
          'total_pub'=>$total_pub,
          'cant_proyectos'=>$cant_proyectos,
          'plan'=>$plan,
          'id'=>$id,
          'publicaciones'=>$publicaciones,
          'proyectos'=>$proyectos
          )
        );
    }

}
?>