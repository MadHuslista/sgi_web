<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;
use SGI\Models\Alumno;
use SGI\Models\Person;
use SGI\Models\PersonStudiesProgram;
use SGI\Models\PersonTeachesSubject;
use SGI\Models\Department;
use SGI\Models\Program;
use SGI\Models\ProgramType;
use SGI\Models\ProgramHasSubject;
use SGI\Models\Subject;
use SGI\Models\PersonEducation;
use SGI\Models\Institution;
use SGI\Models\PersonProgram;


use Illuminate\Database\Capsule\Manager as DB;
class IndicadoresAcademicos extends \SlimController\SlimController
{

    protected $app;

    function __construct() {
        $this->app = Slim::getInstance();
    }

    // Indicadores Academicos 1

    /**
    *Recupera la cantidad de alumnos graduados en programas de magíster
    *estar graduados
    *
    *@author Jorge Courbis
    *@since 1.0
    */

    public function graduadosMagisterAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();
      //Parámetros get
      $params = $this->app->request()->get();
      if(isset($params["inicio"]) && is_numeric($params["inicio"]) &&
      isset($params["termino"]) && is_numeric($params["termino"])){

          if($params["inicio"] <= $params["termino"]){
              $inicio = $params["inicio"];
              $termino = $params["termino"];
          }
          else{
            $inicio = '2011';
            $termino = date('Y') - 1;
          }
      }
      else{
        $inicio = '2011';
        $termino = date('Y') - 1;
      }

      $graduados = array();

      for($i = $termino; $i >= $inicio;  $i--){
        $graduados[$i]['total'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
          ->where('person_studies_program.fecha_titulacion', '=', $i)
          ->where('person_studies_program.calidad_estudiante_id', '=', '2')
          ->whereIn('program.program_type_id','=',[2,3])
          ->count('person_studies_program.program_id');

          $graduados[$i]['ms'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.fecha_titulacion', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '=', '2')
            ->where('program.program_type_id','=','2')
            ->count('person_studies_program.program_id');

            $graduados[$i]['msc'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
              ->where('person_studies_program.fecha_titulacion', '=', $i)
              ->where('person_studies_program.calidad_estudiante_id', '=', '2')
              ->where('program.program_type_id','=','3')
              ->count('person_studies_program.program_id');
      }

      $tipos_programas = ProgramType::where('id','>', '1')
      ->get();

      $this->render('indicadoresveintetreinta-graduadosmagister', array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario,'contadores' => $graduados,
    'tipos_programas' => $tipos_programas, 'inicio' => $inicio, 'termino' => $termino, 'avatar' => $avatar));
    }

    /**
    *Recupera la cantidad de alumnos en programas de postgrado de tipo phd sin
    *estar graduados
    *
    *@author Jorge Courbis
    *@since 1.0
    */
    public function alumnosProgramasPhdAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $params = $this->app->request()->get();
      $rol = $auth->get_role();
      if(isset($params["inicio"]) && is_numeric($params["inicio"]) &&
      isset($params["termino"]) && is_numeric($params["termino"])){

          if($params["inicio"] <= $params["termino"]){
              $inicio = $params["inicio"];
              $termino = $params["termino"];
          }
          else{
            $inicio = '2011';
            $termino = date('Y') - 1;
          }
      }
      else{
        $inicio = '2011';
        $termino = date('Y') - 1;
      }
      $graduados = array();

      for($i = $termino; $i >= $inicio;  $i--){
          $graduados[$i]['total'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.mat_ano', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '!=', '2')
            ->where('program.program_type_id','=','1')
            ->count('person_studies_program.program_id');

          $graduados[$i]['regular'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.mat_ano', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '=', '1')
            ->where('program.program_type_id','=','1')
            ->count('person_studies_program.program_id');

          $graduados[$i]['tesista'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.mat_ano', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '!=', '3')
            ->where('program.program_type_id','=','1')
            ->count('person_studies_program.program_id');

          $graduados[$i]['congelado'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.mat_ano', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '!=', '4')
            ->where('program.program_type_id','=','1')
            ->count('person_studies_program.program_id');

          $graduados[$i]['abandonado'] =  PersonStudiesProgram::join('program', 'person_studies_program.program_id', '=', 'program.id')
            ->where('person_studies_program.mat_ano', '=', $i)
            ->where('person_studies_program.calidad_estudiante_id', '!=', '5')
            ->where('program.program_type_id','=','1')
            ->count('person_studies_program.program_id');
      }

      $this->render('indicadoresveintetreinta-alumnosprogramasphd',
          array('rol' => $rol,'contadores' => $graduados, 'inicio' => $inicio,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario,
        'termino' => $termino, 'avatar' => $avatar));
    }

    /**
    *Recupera los docentes que enseñan programas de magister y que cuentan con
    *grado de doctor
    *
    *@author Jorge Courbis
    *@since 1.0
    */
    // public function docentesGradoPhdAction(){
    //   $auth = new Auth();
    //   if(!$auth->is_valid())
    //     $this->redirect($this->app->urlFor('Login:index'));
    //   $user_id = $auth->get_id();
    //   $nombre_usuario = $auth->get_nombre();
    //   $nombre_completo = $auth->get_name();
    //   $avatar = $auth->get_avatar();
    //   $rol = $auth->get_role();

    //   $resultado = array();

    //   //Se obtienen los programas
    //   $programas = Program::where('id','<>', '28')->orderBy('program.name','asc')->get(array('id', 'name'));
    //   foreach($programas as $p){
    //     $resultado[$p->id]["nombre"] = $p->name;
    //     $resultado[$p->id]["id"] = $p->id;
    //     $resultado[$p->id]["cant_acad"] = PersonProgram::where('program_id', $p->id)->count();
    //     $resultado[$p->id]["cant_acad_phd"] = PersonProgram::join('person','person.id','=','person_has_program.person_id')->
    //     where('program_id', $p->id)->where('person.is_phd', '1')->count();
    //   }
    //   $this->render('indicadoresveintetreinta-docentesmagisterphd2',
    //   array('rol' => $rol,'resultado' => $resultado,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario,
    //   'avatar' => $avatar));

    // }
    
    public function docentesGradoPhdAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-docentes-grado-phd',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera los docentes que enseñan programas de magister y que cuentan con
    *grado de doctor
    *
    *@author Jorge Courbis
    *@since 1.0
    */
    public function docentesMagisterGradoPhdAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();
      $docente = array();
      $resultadodoc = array();
      $resultadoms = array();
      $resultadomsc = array();
      $totalphd = 0;
      $i = 0;
      $j = 0;
      //se obtienen los programas de magister
      $programas = Program::where('program_type_id','1')->get(array('id', 'name'));
      foreach($programas as $p){
        $resultadodoc[$i]['nombre']  = $p->name;
          //Se obtienen los ramos dictados para el programa
          $progs = PersonProgram::where('program_id', '=', $p->id)->distinct()
          ->get(array('person_id'));

          //se obtiene el perfil del docente si su grado es phd tomando como base el ramo
          foreach($progs as $r){
                $docentetmp = PersonProgram::join('person', 'person.id','=', 'person_has_program.person_id')
                ->join('person_has_degree','person.id', '=', 'person_has_degree.person_id')
                ->join('degree', 'person_has_degree.degree_id', '=','person_has_degree.degree_id')
                ->where('person.is_phd', '=',1)
                ->where('person.id', $r->person_id)
                ->first(array('person.name as nombre', 'person.last_name', 'person.m_name',
                'degree.name as grado', 'degree.year as anio', 'person.id'));
                if(!is_null($docentetmp)){
                  $docente[] = $docentetmp;
                  $j++;
                  $totalphd += count($docente);
                }
          }

          $resultadodoc[$i]['docente'] = $docente;
          $resultadodoc[$i]['total'] = $j;
          $docente = array();
          $i++;
          $j = 0;
      }

      $i = 0;
      $j = 0;
      //se obtienen los programas de magister
      $programas = Program::where('program_type_id','2')->get(array('id', 'name'));
      foreach($programas as $p){
        $resultadoms[$i]['nombre']  = $p->name;
          //Se obtienen los ramos dictados para el programa
          $progs = PersonProgram::where('program_id', '=', $p->id)->distinct()
          ->get(array('person_id'));

          //se obtiene el perfil del docente si su grado es phd tomando como base el ramo
          foreach($progs as $r){
            $docentetmp = PersonProgram::join('person', 'person.id','=', 'person_has_program.person_id')
            ->join('person_has_degree','person.id', '=', 'person_has_degree.person_id')
            ->join('degree', 'person_has_degree.degree_id', '=','person_has_degree.degree_id')
            ->where('person.is_phd', '=',1)
            ->where('person.id', $r->person_id)
            ->first(array('person.name as nombre', 'person.last_name', 'person.m_name',
            'degree.name as grado', 'degree.year as anio', 'person.id'));
                if(!is_null($docentetmp)){
                  $docente[] = $docentetmp;
                  $j++;
                  $totalphd += count($docente);
                }
          }

          $resultadoms[$i]['docente'] = $docente;
          $resultadoms[$i]['total'] = $j;
          $docente = array();
          $i++;
          $j = 0;
      }
      $i = 0;
      $j = 0;

      //se obtienen los programas de magister
      $programas = Program::where('program_type_id','3')->get(array('id', 'name'));
      foreach($programas as $p){
        $resultadomsc[$i]['nombre']  = $p->name;
          //Se obtienen los ramos dictados para el programa
          $progs = PersonProgram::where('program_id', '=', $p->id)->distinct()
          ->get(array('person_id'));

          //se obtiene el perfil del docente si su grado es phd tomando como base el ramo
          foreach($progs as $r){
            $docentetmp = PersonProgram::join('person', 'person.id','=', 'person_has_program.person_id')
            ->join('person_has_degree','person.id', '=', 'person_has_degree.person_id')
            ->join('degree', 'person_has_degree.degree_id', '=','person_has_degree.degree_id')
            ->where('person.is_phd', '=',1)
            ->where('person.id', $r->person_id)
            ->first(array('person.name as nombre', 'person.last_name', 'person.m_name',
            'degree.name as grado', 'degree.year as anio', 'person.id'));
                if(!is_null($docentetmp)){
                  $docente[] = $docentetmp;
                  $j++;
                  $totalphd += count($docente);
                }
          }

          $resultadomsc[$i]['docente'] = $docente;
          $resultadomsc[$i]['total'] = $j;
          $docente = array();
          $i++;
          $j = 0;
      }

      $totalphd = count($docente);
      //var_dump($resultado); return;
      $this->render('indicadoresveintetreinta-docentesmagisterphd',
      array('rol' => $rol,'resultadodoc' => $resultadodoc,'resultadoms' => $resultadoms,'resultadomsc' => $resultadomsc,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario,
      'totalphd' => $totalphd, 'avatar' => $avatar));
    }

    ######################################################################################################################
    ######################################## INDICADORES DE ACADEMICOS 1 #################################################  
    ###################################################################################################################### 

    /**
    *Recupera las matriculas de primer año
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function matriculaNuevaPrimerAnioAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-matricula-nueva-anio-1',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera las vacantes ofrecidas vs la matricula efectiva
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function vacantesOfrecidasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $vac = (object)array();
      $vac->anio5x = 45;
      $vac->anio5y = 44;
      $vac->anio5yx = round(100*$vac->anio5y/$vac->anio5x);
      $vac->anio4x = 45;
      $vac->anio4y = 47;
      $vac->anio4yx = round(100*$vac->anio4y/$vac->anio4x);
      $vac->anio3x = 65;
      $vac->anio3y = 60;
      $vac->anio3yx = round(100*$vac->anio3y/$vac->anio3x);
      $vac->anio2x = 65;
      $vac->anio2y = 61;
      $vac->anio2yx = round(100*$vac->anio2y/$vac->anio2x);
      $vac->anio1x = 60;
      $vac->anio1y = 58;
      $vac->anio1yx = round(100*$vac->anio1y/$vac->anio1x);

      $this->render('indicadoresacademicos-vacantes-ofrecidas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'vac' => $vac));

    }

    /**
    *Recupera la caraterizacion de los estudiantes por año
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function caracterizacionEstudiantesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-caracterizacion-estudiantes',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera la tasa de retencion en el primer año
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function tasaRetencionPrimerAnioAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $ret_primer_anio = (object)array();
      /* Datos Dinámicos 
      # Información insuficiente en tabla Alumnos
      */

      /* Datos Estáticos */
      $ret_primer_anio->anio_5 = 80;
      $ret_primer_anio->anio_4 = 84;
      $ret_primer_anio->anio_3 = 70;
      $ret_primer_anio->anio_2 = 89;
      $ret_primer_anio->anio_1 = 85;

      $this->render('indicadoresacademicos-tasa-retencion-anio-1',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'ret_primer_anio' => $ret_primer_anio));

    }

    /**
    *Recupera la tasa de retencion total
    *
    *@author Guillermo Briceño
    *@since 1.0
    */


    public function tasaRetencionTotalAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $ret_total = (object)array();
      /* Datos Dinámicos
      $anio = intval(date('Y'));
      $ret_total->anio_5 = floor(100*Alumno::where('anio_ingreso_carrera',$anio-5)->whereNotIn('calidad', ['Abandona Estudios', 'Eliminado', 'Renuncia Universidad', 'Retiro Definitivo Académico', 'Retiro Definitivo Voluntario'])->count()/Alumno::where('anio_ingreso_carrera',$anio-5)->count());
      $ret_total->anio_4 = floor(100*Alumno::where('anio_ingreso_carrera',$anio-4)->whereNotIn('calidad', ['Abandona Estudios', 'Eliminado', 'Renuncia Universidad', 'Retiro Definitivo Académico', 'Retiro Definitivo Voluntario'])->count()/Alumno::where('anio_ingreso_carrera',$anio-4)->count());
      $ret_total->anio_3 = floor(100*Alumno::where('anio_ingreso_carrera',$anio-3)->whereNotIn('calidad', ['Abandona Estudios', 'Eliminado', 'Renuncia Universidad', 'Retiro Definitivo Académico', 'Retiro Definitivo Voluntario'])->count()/Alumno::where('anio_ingreso_carrera',$anio-3)->count());
      $ret_total->anio_2 = floor(100*Alumno::where('anio_ingreso_carrera',$anio-2)->whereNotIn('calidad', ['Abandona Estudios', 'Eliminado', 'Renuncia Universidad', 'Retiro Definitivo Académico', 'Retiro Definitivo Voluntario'])->count()/Alumno::where('anio_ingreso_carrera',$anio-2)->count());
      $ret_total->anio_1 = floor(100*Alumno::where('anio_ingreso_carrera',$anio-1)->whereNotIn('calidad', ['Abandona Estudios', 'Eliminado', 'Renuncia Universidad', 'Retiro Definitivo Académico', 'Retiro Definitivo Voluntario'])->count()/Alumno::where('anio_ingreso_carrera',$anio-1)->count());
      */

      /* Datos Estáticos */
      $ret_total->anio_5 = 48;
      $ret_total->anio_4 = 64;
      $ret_total->anio_3 = 55;
      $ret_total->anio_2 = 67;
      $ret_total->anio_1 = 85;

      $this->render('indicadoresacademicos-tasa-retencion-total',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'ret_total' => $ret_total));

    }

    /**
    *Recupera la progresión academica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */


     public function progresionAcademicaAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-progresion-academica',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

     /**
    *Recupera la desercion academica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */


     public function desercionAcademicaAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-desercion-academica',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera la eliminacion academica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */


     public function eliminacionAcademicaAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-eliminacion-academica',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

     /**
    *Recupera la tiempo de permanencia academica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function tiempoPermanenciaAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-tiempo-permanencia',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera la tasa de egreso por cohorte
    *
    *@author Guillermo Briceño
    *@since 1.0
    */


    public function tasaEgresoPorCohorteAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $tasa_egreso = (object)array();
      /* Datos Dinámicos      
      $anio = ['2006','2007','2008','2009','2010'];
      $tasa_egreso->anio_0 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[0])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[0])->count());
      $tasa_egreso->anio_1 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[1])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[1])->count());
      $tasa_egreso->anio_2 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[2])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[2])->count());
      $tasa_egreso->anio_3 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[3])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[3])->count());
      $tasa_egreso->anio_4 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[4])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[4])->count());
      */
      /* Datos Estáticos */
      $tasa_egreso->anio_0 = '';
      $tasa_egreso->anio_1 = '';
      $tasa_egreso->anio_2 = '';
      $tasa_egreso->anio_3 = '';
      $tasa_egreso->anio_4 = '';

      $this->render('indicadoresacademicos-tasa-egreso-por-cohorte',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'tasa_egreso' => $tasa_egreso));

    }

     /**
    *Recupera la tasa de titulacion por cohorte
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function tasaTitulacionPorCohorteAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $tasa_tit = (object)array();
      /* Datos Dinámicos      
      $anio = ['2006','2007','2008','2009','2010'];
      $tasa_tit->anio_0 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[0])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[0])->count());
      $tasa_tit->anio_1 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[1])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[1])->count());
      $tasa_tit->anio_2 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[2])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[2])->count());
      $tasa_tit->anio_3 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[3])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[3])->count());
      $tasa_tit->anio_4 = floor(100*Alumno::where('anio_ingreso_carrera',$anio[4])->whereNotNull('anio_egreso')->count()/Alumno::where('anio_ingreso_carrera',$anio[4])->count());
      */
      /* Datos Estáticos */
      $tasa_tit->anio_0 = 32;
      $tasa_tit->anio_1 = 16;
      $tasa_tit->anio_2 = 15;
      $tasa_tit->anio_3 = 4;
      $tasa_tit->anio_4 = 0;

      $this->render('indicadoresacademicos-tasa-titulacion-por-cohorte',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'tasa_tit' => $tasa_tit));

    }

     /**
    *Recupera la tasa de titulacion oportuna por cohorte
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function tasaTitulacionOportunaPorCohorteAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $tasa_tit_op = (object)array();
      /* Datos Estáticos */
      $tasa_tit_op->anio_0 = 16;
      $tasa_tit_op->anio_1 = 3;
      $tasa_tit_op->anio_2 = 8;
      $tasa_tit_op->anio_3 = 0;
      $tasa_tit_op->anio_4 = 0;

      $this->render('indicadoresacademicos-tasa-titulacion-oportuna-por-cohorte',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'tasa_tit_op' => $tasa_tit_op));

    }

    ######################################################################################################################
    ######################################## INDICADORES DE ACADEMICOS 2 #################################################  
    ###################################################################################################################### 


    /**
    *Recupera el tiempo real de titulación
    *
    *@author Guillermo Briceño
    *@since 1.0
    */
    // Obtiene el tiempo real de titulacion para 5 años consecutivos
    public function tiempoRealTitulacionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      /* Datos Dinámicos
      // Años relevantes para el indicador
      $años = ['2007','2008','2009','2010','2011'];
      // Se considera graduacion en 1er semestre desde enero hasta julio
      $sem1 = ['Jan','Feb','Mar','Apr','May','Jun','Jul'];
      // Se considera graduacion en 2do semestre desde agosto hasta diciembre
      $sem2 = ['Aug','Sep','Oct','Nov','Dec'];
      $graduaciones = Alumno::whereIn('anio_ingreso_carrera', $años)->whereNotNull('fecha_graduacion')->orderBy('anio_ingreso_carrera','asc')->get();

      $tiempo_real = (object)array();
      $año1 = [];
      $año2 = [];
      $año3 = [];
      $año4 = [];
      $año5 = [];
      
      foreach ($graduaciones as $g) {
        if($g->anio_ingreso_carrera == $años[0]){
          if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem1))
            array_push($año1, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera)));
          else if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem2))
            array_push($año1, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera))+1);
        }
        else if($g->anio_ingreso_carrera == $años[1]){
          if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem1))
            array_push($año2, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera)));
          else if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem2))
            array_push($año2, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera))+1);
        }
        else if($g->anio_ingreso_carrera == $años[2]){
          if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem1))
            array_push($año3, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera)));
          else if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem2))
            array_push($año3, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera))+1);
        }
        else if($g->anio_ingreso_carrera == $años[3]){
          if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem1))
            array_push($año4, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera)));
          else if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem2))
            array_push($año4, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera))+1);
        }
        else if($g->anio_ingreso_carrera == $años[4]){
          if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem1))
            array_push($año5, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera)));
          else if(in_array(date_format(date_create($g->fecha_graduacion),'M'), $sem2))
            array_push($año5, 2*(intval(date_format(date_create($g->fecha_graduacion),'Y'))-intval($g->anio_ingreso_carrera))+1);
        }
      }

      $tiempo_real = (object)array();
      $tiempo_real->año1 = empty($año1) ? '' : round(array_sum($año1)/count($año1),1);
      $tiempo_real->año2 = empty($año2) ? '' : round(array_sum($año2)/count($año2),1);
      $tiempo_real->año3 = empty($año3) ? '' : round(array_sum($año3)/count($año3),1);
      $tiempo_real->año4 = empty($año4) ? '' : round(array_sum($año4)/count($año4),1);
      $tiempo_real->año5 = empty($año5) ? '' : round(array_sum($año5)/count($año5),1);
      $tiempo_real->prom = round(array_sum(array($tiempo_real->año1,$tiempo_real->año2,$tiempo_real->año3,$tiempo_real->año4,$tiempo_real->año5))/count(array_filter(array($tiempo_real->año1,$tiempo_real->año2,$tiempo_real->año3,$tiempo_real->año4,$tiempo_real->año5))),2);
      */

      /* Datos Estáticos */
      $tiempo_real = (object)array();
      $tiempo_real->año1 = 15.3;
      $tiempo_real->año2 = 16.2;
      $tiempo_real->año3 = 15.2;
      $tiempo_real->año4 = 15.0;
      $tiempo_real->año5 = '';
      $tiempo_real->prom = 15.43;
      
      $this->render('indicadoresacademicos-tiempo-real-titulacion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'tiempo_real' => $tiempo_real));
    }

    /**
    *Recupera la empleabilidad laboral por cohorte
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function empleabilidadLaboralCohorteAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-empleabilidad-laboral-cohorte',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera el tiempo promedio de insercion por cohorte
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function tiempoInsercionCohorteAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-tiempo-insercion-cohorte',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera la tabla de las asignaturas de la carrera Ingenieria Civil Metalurgica
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function tablaAsignaturasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-tabla-asignaturas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera el tiempo promedio de insercion por cohorte
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function asignaturasCriticasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-asignaturas-criticas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera los integrantes de evaluacion del plan de estudio
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function integrantesEvaluacionPlanEstudioAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-integrantes-evaluacion-plan-estudio',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera los integrantes de evaluacion del desarrollo de asignaturas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function integrantesEvaluacionDesarrolloAsignaturasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-integrantes-evaluacion-desarrollo-asignaturas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

     /**
    *Recupera los datos sobre el Sistema de Selección y Admisión Alumnos Regulares
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

     public function seleccionAdmisionRegularAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-seleccion-y-admision-regular',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    /**
    *Recupera los datos sobre el Sistema de Selección y Admisión Alumnos Especiales
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function seleccionAdmisionEspecialAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-seleccion-y-admision-especial',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

      /**
    *Recupera las actividades de mejoramiento academico
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function actividadesMejoramientoAcademicoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresacademicos-actividades-mejoramiento-academicos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }



    ###########################################################################################################################

    
/*
    public function satisfactionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-satisfaction',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }


    public function mastersAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-masters',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }


    public function phdsAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-phds',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    public function pubintAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-pubint',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }


    public function citasAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-citas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // IC 2: Number of Master's degrees granted
    public function mastersGrantedAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-masters-granted',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // IC 3: Number of PhD in engineering degrees granted
    public function phdEngGrantedAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-phd-engineering-granted',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // IM 12: Postgraduate programs  (Doctorate and Master) in engineering related fields
    public function postEngProgramsAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresveintetreinta-postgraduate-engineering-programs',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Procedimientos 2030 Gestion
    public function procGestionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-procedimientos-gestion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Procedimientos 2030 Calculo Indicadores
    public function procIndicadoresAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-procedimientos-indicadores',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Actas Reuniones
    public function actasReunionesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-actas-reuniones',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Informes Corfo Informe
    public function corfoInformeAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-informes-corfo',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Informes Corfo Anexos
    public function corfoAnexosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-informes-corfo-anexos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
    
    // Ficha Doctorado Dual
    public function fichaDoctoradoDualAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('ingenieria2030-ficha-doctorado',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }
*/
}

?>