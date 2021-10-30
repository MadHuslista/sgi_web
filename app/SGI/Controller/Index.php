<?php

namespace SGI\Controller;

use SGI\Lib\Auth;
use Carbon\Carbon;
use \DateTime;
use PHPExcel;

use SGI\Models\Calendario;
use SGI\Models\TipoCalendario; 
use SGI\Models\Presupuesto;


class Index extends \SlimController\SlimController
{


    function getDatosSIGA($query){

      $datos = [];
      $conn = oci_connect("carlos_vera", "*lk2ndst{y", "//orcl-scan.dti.utfsm.cl/orcl.dti.utfsm.cl", 'UTF8');
        if (!$conn) {
           $m = oci_error();
           
        }
        else {
          //generar un select desde la vista asignada que deberá ser entregada por los administradores
          //TODO: sanitización de parámetros
          $stid = oci_parse($conn, $query);
  
          //Ejecutar la consulta
          oci_execute($stid,OCI_DEFAULT);
          while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
              foreach ($row as $item) {
                  array_push($datos, $row);
              }
          }
          //cerramos la conexión
          oci_close($conn);
  
        }

        return $datos;
    }

    public function testingAction(){
      
      dd($this->getDatosSIGA("select MATRICULA_OTRA_CARRERA from V_ALUMNOS_METALURGICA "));
    }
    public function indexAction()
    {

		    $auth = new Auth();
		    if(!$auth->is_valid())
			     $this->redirect($this->app->urlFor('Login:index'));
        $user_id = $auth->get_id();
        $nombre_usuario = $auth->get_nombre();
        $nombre_completo = $auth->get_name();
        $avatar = $auth->get_avatar();
        $rol = $auth->get_role();

        

        $hoy = Carbon::now();
        $calendarios=Calendario::join('tipo_calendario','calendario.tipo_calendario_id','=','tipo_calendario.id')
        ->where('tipo_calendario.id',1)
        ->whereDate('calendario.fecha_final', '>=', $hoy)
        ->get(['calendario.titulo as titulo','calendario.descripcion as descripcion','calendario.fecha_inicio as fecha_inicio','calendario.fecha_final as fecha_final','calendario.id', 'calendario.fecha_mostrar', 'calendario.categoria']); 

        foreach ($calendarios as $cal)
        {
          $fechaInicio = $cal->fecha_inicio;
          $fechaTermino = $cal->fecha_final;
          $id_cal = $cal->id;
          // Si es que la actividad es por el dia
          if ($fechaTermino == $fechaInicio)
          {
             $day = date("d",strtotime($fechaInicio));
             $month = date("F",strtotime($fechaInicio));

             $fechaMostrar = $day." ". $month;

             Calendario::insertarFecha($fechaMostrar,$id_cal); 
          }
          //  Si la actividad es por varios dias
          else
          {
            $mesInicio = date("m",strtotime($fechaInicio));
            $mesTermino = date("m",strtotime($fechaTermino));
            $diaInicio = date("d",strtotime($fechaInicio));
            $diaTermino = date("d",strtotime($fechaTermino));
            $day= $diaInicio."-".$diaTermino ;

            if ($mesInicio == $mesTermino)
            {
                $month = date("F",strtotime($fechaInicio));
                $fechaMostrar = $day." ". $month;
                Calendario::insertarFecha($fechaMostrar,$id_cal); 
            }
            else
            {
              $month = date("F",strtotime($fechaTermino));
              $fechaMostrar = $day." ". $month;
              Calendario::insertarFecha($fechaMostrar,$id_cal); 
            }
          }  
        }

        
        $datos_grafico = [0,0,0,0,0];

        $anios = [intval(date('Y',strtotime('-4 years'))), 
        intval(date('Y',strtotime('-3 years'))), 
        intval(date('Y',strtotime('-2 years'))), 
        intval(date('Y',strtotime('-1 years'))), 
        intval(date('Y')) ];




        $alumnos = $this->getDatosSIGA("select AÑO_INGRESO_CARRERA from V_ALUMNOS_METALURGICA");
        
        foreach($alumnos as $a){
           
          $an = $a["AÑO_INGRESO_CARRERA"];
          $offset = $an - $anios[0];
          if($offset < 0) $offset = 0;
          $datos_grafico[ $offset]++;
        }
        
        for($i=0; $i < count($datos_grafico) -1 ; $i++){
          $datos_grafico[$i+1] = $datos_grafico[$i]+ $datos_grafico[$i+1];
        }

        $anios = array_values($anios);


        //presupuesto

        $presupuesto = Presupuesto::where('anio', intval(date('Y')))->get();

        $p_total = $presupuesto->sum(function($p){
          return $p->ppto_enero + $p->ppto_febrero+
          $p->ppto_marzo + $p->ppto_abril + $p->ppto_mayo + $p->ppto_junio + 
          $p->ppto_julio + $p->ppto_agosto+ $p->ppto_septiembre + $p->ppto_octubre +
          $p->ppto_noviembre + $p->ppto_diciembre;
        });
        

        $array_respuesta =  array(
          'rol' => $rol,
          'user_id' => $user_id, 
          'nombre_completo' => $nombre_completo, 
          'nombre_usuario' => $nombre_usuario,
          'avatar' => $avatar, 
          'calendarios' => $calendarios,

          'total_matriculas_nuevas' => count($this->getDatosSIGA("select CALIDAD from V_ALUMNOS_METALURGICA where AÑO_INGRESO_CARRERA='".date("Y"). "'")),
          'total_matriculas' => count($alumnos),
          'presupuesto' => 0,

          'anios_array' => $anios,
          'datos_metalurgica_array' => $datos_grafico,

          'anios' => json_encode($anios),
          'datos_metalurgica' => json_encode($datos_grafico),

          'presupuesto' => $p_total

        );
        
        $this->render('index',$array_respuesta);
    }

    public function construccionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();
      $this->render('construccion', array('user_id' => $user_id,'rol' => $rol,
    'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

    public function prohibidoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();
      $this->render('prohibido', array('user_id' => $user_id,'rol' => $rol,
    'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
    }

}
