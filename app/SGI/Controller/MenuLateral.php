<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;
use SGI\Models\Person;

use SGI\Models\Proyecto;
use SGI\Models\Publicacion;

use PHPExcel;

use Illuminate\Database\Capsule\Manager as DB;

class MenuLateral extends \SlimController\SlimController
{

    protected $app;
    function __construct() {
        $this->app = Slim::getInstance();
    }

    /**
    *Recupera la malla curricular de las asignaturas de la carrera Ingenieria Civil Metalurgica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function mallaCurricularAsignaturasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('malla-curricular-5406',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    // Actas Consejo Departamento
    // Sebastián Gallardo
    public function actasConsejoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('actas-consejo',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */



    public function pdeDimmAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('pde-dimm',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function reglamentosEstudiantesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('reglamentos-estudiantes',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function reglamentosPersonalAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('reglamentos-personal',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function reglamentosProcesosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('reglamentos-procesos-docentes',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function reglamentosInstitucionCarreraAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('reglamentos-institucion-carrera',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Reglamentos Estudiantes
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function reglamentosTitulacionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('reglamentos-titulacion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    //INFORMACION GENERAL
    public function planEstrategicoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('plan-estrategico',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function estatutosInstitucionalesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('estatutos-institucionales',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function proyectoEducativoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('proyecto-educativo',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function procesoAdmisionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('proceso-admision',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function tecnicasEstudioAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('tecnicas-estudio',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function pruebasAlcanceNacionalAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('pruebas-alcance-nacional',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function practicasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('practicas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    //DOCUMENTOS
    //
    
    public function lineamientosInstitucionalesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('lineamientos-institucionales',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function vinculacionMedioAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('plan-vinculacion-medio',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }
 
    public function politicasAmenazasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('politicas-amenazas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function politicasRecursosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('politicas-recursos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function criterio8Action(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('criterio8',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function accesibilidadUniversalAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('accesibilidad-universal',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function estatutosFederacionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      

      $this->render('estatutos-federacion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }
}
?>