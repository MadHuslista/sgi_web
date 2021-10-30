<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;

use SGI\Models\Acreditacion;
use SGI\Models\Instalacion;
use SGI\Models\Inversion;
use SGI\Models\Organizacion;
use SGI\Models\Cuenta;
use SGI\Models\Presupuesto;
use SGI\Models\Movimiento;

use PHPExcel;
use PHPExcel_IOFactory;
use DateTime;
use Illuminate\Support\Facades\Session;

use Illuminate\Database\Capsule\Manager as DB;

class PresupuestoController extends \SlimController\SlimController
{

    protected $app;
    function __construct() {
        $this->app = Slim::getInstance();
    }


    public function VerGraficosAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $app = Slim::getInstance();
      $rol = $auth->get_role();

      if ($rol != 2){
        $this->redirect($this->app->urlFor('Index:index'));
      }

      $request = $app->request;

      $presupuestos = Presupuesto::join('organizacion','presupuestos.id_organizacion','=','organizacion.id')
      ->groupBy(['anio','id_organizacion'])
      ->get(['presupuestos.id_organizacion as id','anio','organizacion.codigo as codigo','organizacion.nombre as nombre']);
      $this->render('ver-graficos', [
        'presupuestos' => $presupuestos,

        'rol' => $rol,
  			'nombre_completo'=>$nombre_completo,
  			'nombre_usuario'=>$nombre_usuario,

  			'avatar' => $avatar,
  			'user_id' => $user_id
      ]);
    }

    public function YaCargadoAction(){
      date_default_timezone_set('America/Santiago');

      $app = Slim::getInstance();
      $params = $app->request->post();

      $organizacion = Organizacion::find($params['organizacion']);

      $presupuesto = Presupuesto::where('anio',date('Y'))->where('id_organizacion',$organizacion->id)->get();

      if(count($presupuesto) > 0) $ya_cargado = 1;
      else $ya_cargado = 0;

      $response = json_encode($ya_cargado);
      echo $response;

    }

    public function getCuentasAction(){

      $app = Slim::getInstance();
      $params = $app->request->post();

      $presupuesto = explode("-",$params["organizacion"]);
      $organizacion = $presupuesto[0];
      $anio = $presupuesto[1];
      


      $cuentas = Presupuesto::join('cuentas','presupuestos.cod_cuenta','=','cuentas.id')->where('presupuestos.id_organizacion',$organizacion)
                ->where('presupuestos.anio',$anio)
                ->get(['cuentas.id as id','cuentas.codigo as codigo','cuentas.nombre as nombre']);
      
      $response = json_encode($cuentas);
      echo $response;


    }

    public function getDatosGraficoAction(){
      $app = Slim::getInstance();
      $params = $app->request->post();

      $p = explode("-",$params["presupuesto"]);
      $organizacion = $p[0];
      $anio = $p[1];
      $cuenta = $params["cuenta"];

      $presupuesto = Presupuesto::where("id_organizacion",$organizacion)->where("anio",$anio)->where("cod_cuenta",$cuenta)->get()->first();


      $presupuestos_mensuales = [
        $presupuesto->ppto_enero,
        $presupuesto->ppto_febrero,
        $presupuesto->ppto_marzo,
        $presupuesto->ppto_abril,
        $presupuesto->ppto_mayo,
        $presupuesto->ppto_junio,
        $presupuesto->ppto_julio,
        $presupuesto->ppto_agosto,
        $presupuesto->ppto_septiembre,
        $presupuesto->ppto_octubre,
        $presupuesto->ppto_noviembre,
        $presupuesto->ppto_diciembre
      ];

      $ejecutado_mensuales = [0,0,0,0,0,0,0,0,0,0,0,0];

      $movimientos = Movimiento::where('id_organizacion',$organizacion)->where('id_cuenta',$cuenta)->whereYear('fecha','=',$anio)->get();

      foreach($movimientos as $movimiento){
       $valor = 0 - $movimiento->valor;
        $i = intval(date('n',strtotime($movimiento->fecha))) - 1;
        $ejecutado_mensuales[$i]+= $valor;
      }

      $datos = [$presupuestos_mensuales, $ejecutado_mensuales, $anio];


      
      $response = json_encode($datos);
      echo $response;
      
      
    }


    public function cargarPresupuestoAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $app = Slim::getInstance();
      $rol = $auth->get_role();

      if ($rol != 2){
        $this->redirect($this->app->urlFor('Index:index'));
      }

      $request = $app->request;

      $organizaciones = Organizacion::get();



      $this->render('cargar-presupuesto',array(
        'organizaciones' => $organizaciones,


        'rol' => $rol,
  			'nombre_completo'=>$nombre_completo,
  			'nombre_usuario'=>$nombre_usuario,

  			'avatar' => $avatar,
  			'user_id' => $user_id
      ));
    }

    public function cargarPresupuestoAnualAction(){

      date_default_timezone_set('America/Santiago');
      $auth = new Auth();
  		if(!$auth->is_valid())
  			 $this->redirect($this->app->urlFor('Login:index'));
      $app = Slim::getInstance();
      $params = $app->request->post();
      $organizacion = $params["organizacion"];
      $anio = date('Y');

      $inputFileType = 'Excel2007';

      $inputFileName = $_FILES["archivo"]["tmp_name"];

      $name = $_FILES['archivo']['name'];
      $ext = pathinfo($name, PATHINFO_EXTENSION);

      if ( $ext != "xlsx"){
          $app->flash('error', 'Archivo debe tener extension .xlsx');
          $this->redirect($this->app->urlFor('PresupuestoController:cargarPresupuesto'));
      }

      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objReader->setReadDataOnly(true);


      $objPHPExcel = $objReader->load($inputFileName);
      $worksheet = $objPHPExcel->getActiveSheet();

      /*A5:A19 Remuneraciones
        A21:A73 Gastos Operacionales
        A75:A82 Inversiones
        A84:A93 Otros

        Leer desde D hasta O
      */

      for ($i = 1; $i <= 95 ; $i++){
        $cuenta = Cuenta::where('codigo',$worksheet->getCell('A'.$i)->getValue())->get()->first();
        if(!is_null($cuenta)){
            $all_null = 1;

            //leer presupuestos
            $ppto_enero = $worksheet->getCell('D'.$i)->getCalculatedValue();
            if (!is_null($ppto_enero) && $ppto_enero != 0) $all_null = 0;

            $ppto_febrero= $worksheet->getCell('E'.$i)->getCalculatedValue();
            if (!is_null($ppto_febrero) && $ppto_febrero != 0) $all_null = 0;

            $ppto_marzo = $worksheet->getCell('F'.$i)->getCalculatedValue();
            if (!is_null($ppto_marzo) && $ppto_marzo != 0) $all_null = 0;

            $ppto_abril = $worksheet->getCell('G'.$i)->getCalculatedValue();
            if (!is_null($ppto_abril) && $ppto_abril != 0) $all_null = 0;

            $ppto_mayo = $worksheet->getCell('H'.$i)->getCalculatedValue();
            if (!is_null($ppto_mayo) && $ppto_mayo != 0) $all_null = 0;

            $ppto_junio = $worksheet->getCell('I'.$i)->getCalculatedValue();
            if (!is_null($ppto_junio) && $ppto_junio != 0) $all_null = 0;

            $ppto_julio = $worksheet->getCell('J'.$i)->getCalculatedValue();
            if (!is_null($ppto_julio) && $ppto_julio != 0) $all_null = 0;

            $ppto_agosto = $worksheet->getCell('K'.$i)->getCalculatedValue();
            if (!is_null($ppto_agosto) && $ppto_agosto != 0) $all_null = 0;

            $ppto_septiembre = $worksheet->getCell('L'.$i)->getCalculatedValue();
            if (!is_null($ppto_septiembre) && $ppto_septiembre != 0) $all_null = 0;

            $ppto_octubre = $worksheet->getCell('M'.$i)->getCalculatedValue();
            if (!is_null($ppto_octubre) && $ppto_octubre != 0) $all_null = 0;

            $ppto_noviembre = $worksheet->getCell('N'.$i)->getCalculatedValue();
            if (!is_null($ppto_noviembre) && $ppto_noviembre != 0) $all_null = 0;

            $ppto_diciembre = $worksheet->getCell('O'.$i)->getCalculatedValue();
            if (!is_null($ppto_diciembre) && $ppto_diciembre != 0) $all_null = 0;


            if (!$all_null){
              if (is_null($ppto_enero)) $ppto_enero = 0;
              if (is_null($ppto_febrero)) $ppto_febrero = 0;
              if (is_null($ppto_marzo)) $ppto_marzo = 0;
              if (is_null($ppto_abril)) $ppto_abril= 0;
              if (is_null($ppto_mayo)) $ppto_mayo = 0;
              if (is_null($ppto_junio)) $ppto_junio = 0;
              if (is_null($ppto_julio)) $ppto_julio = 0;
              if (is_null($ppto_agosto)) $ppto_agosto = 0;
              if (is_null($ppto_septiembre)) $ppto_septiembre = 0;
              if (is_null($ppto_octubre)) $ppto_octubre = 0;
              if (is_null($ppto_noviembre)) $ppto_noviembre = 0;
              if (is_null($ppto_diciembre)) $ppto_diciembre = 0;


              $query = Presupuesto::updateOrCreate(
                [
                  'anio' => $anio,
                  'id_organizacion' => $organizacion,
                  'cod_cuenta' => $cuenta->id
                ],
                [
                  'ppto_enero' => $ppto_enero,
                  'ppto_febrero' => $ppto_febrero,
                  'ppto_marzo' => $ppto_marzo,
                  'ppto_abril' => $ppto_abril,
                  'ppto_mayo' => $ppto_mayo,
                  'ppto_junio' => $ppto_junio,
                  'ppto_julio' => $ppto_julio,
                  'ppto_agosto' => $ppto_agosto,
                  'ppto_septiembre' => $ppto_septiembre,
                  'ppto_octubre' => $ppto_octubre,
                  'ppto_noviembre' => $ppto_noviembre,
                  'ppto_diciembre' => $ppto_diciembre
                ]
              );



            }
        }
      }


      $app->flash('success', '¡Carga realizada correctamente!');


      $this->redirect($this->app->urlFor('PresupuestoController:cargarPresupuesto'));
    }



    public function nuevoMovimientoAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $app = Slim::getInstance();
      $rol = $auth->get_role();

      if ($rol != 2){
        $this->redirect($this->app->urlFor('Index:index'));
      }

      $request = $app->request;

      $organizaciones = Organizacion::get();



      $this->render('cargar-movimiento',array(
        'organizaciones' => $organizaciones,


        'rol' => $rol,
        'nombre_completo'=>$nombre_completo,
        'nombre_usuario'=>$nombre_usuario,

        'avatar' => $avatar,
        'user_id' => $user_id
      ));
    }

    public function cargarMovimientoAction(){
      date_default_timezone_set('America/Santiago');
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $app = Slim::getInstance();
      $params = $app->request->post();
      $organizacion = $params["organizacion"];

      $errores = "";
      $inputFileType = 'Excel2007';

      $inputFileName = $_FILES["archivo"]["tmp_name"];

      $name = $_FILES['archivo']['name'];
      $ext = pathinfo($name, PATHINFO_EXTENSION);

      if ( $ext != "xlsx"){
          $app->flash('error', 'Archivo debe tener extension .xlsx');
          $this->redirect($this->app->urlFor('PresupuestoController:nuevoMovimiento'));
      }

      $objReader = PHPExcel_IOFactory::createReader($inputFileType);
      $objReader->setReadDataOnly(true);


      $objPHPExcel = $objReader->load($inputFileName);
      $worksheet = $objPHPExcel->getActiveSheet();

      $i = 3;

    //  dd($worksheet->getCell('A9')->getValue());

      while(!is_null($worksheet->getCell('A'.$i)->getValue())){
        $cuenta = Cuenta::where('codigo',$worksheet->getCell('A'.$i)->getValue())->get()->first();

        if(!is_null($cuenta)){
          $rut = $worksheet->getCell('E'.$i)->getValue();
          $numdoc = $worksheet->getCell('F'.$i)->getValue();

          $format = "d/m/y H:i:s";
          $fecha = DateTime::createFromFormat($format, $worksheet->getCell('G'.$i)->getValue());
          $fecha = $fecha->format('Y-m-d H:i:s');
          $valor = $worksheet->getCell('I'.$i)->getValue();

          $mov = Movimiento::where(['id_organizacion' => $organizacion, 'rut' => $rut, 'num_doc' => $numdoc, 'fecha' => $fecha, 'valor' => $valor])->get();

          if (count($mov) == 0){
            Movimiento::create([
              'id_organizacion' => $organizacion,
              'id_cuenta' => $cuenta->id,
              'fecha' => $fecha,
              'nombre_movimiento' => $worksheet->getCell('B'.$i)->getValue(),
              'nDocBanner' => $worksheet->getCell('C'.$i)->getValue(),
              'tipo_doc' => $worksheet->getCell('D'.$i)->getValue(),
              'rut' => $rut,
              'num_doc' => $numdoc,
              'identificador' => $worksheet->getCell('H'.$i)->getValue(),
              'valor' => $valor,
              'estado' => $worksheet->getCell('J'.$i)->getValue()
            ]);
          }else{
            $error = '<tr> <td> <div class="alert alert-danger">El movimiento de la fila '.$i.' de su archivo ya fue ingresado en una carga anterior. </div> </td> </tr>';
            $errores .= $error;

          }
        }



        $i++;
      }


      if($errores == ""){
        $app->flash('success', '¡Carga realizada correctamente!');
        $this->redirect($this->app->urlFor('PresupuestoController:nuevoMovimiento'));
      }else{

        $this->render('errores-carga-movimientos',array(
          'errores' => $errores



        ));
      }







    }


}
?>
