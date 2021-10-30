<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;
use SGI\Models\Person;

## Modelos de vinculacion
use SGI\Models\ExAlumnos;
use SGI\Models\Empresa;
use SGI\Models\Calendario;
use SGI\Models\Practicas;
use SGI\Models\AreaDesempenio;
use SGI\Models\CentroExAlumnos;
use SGI\Models\Memoria;
use SGI\Models\Profesor;
use SGI\Models\Alumno;

use Carbon\Carbon;
use \DateTime;
use PHPExcel;

use Illuminate\Database\Capsule\Manager as DB;
class IndicadoresVinculacion extends \SlimController\SlimController
{

    protected $app;

    function __construct() {
        $this->app = Slim::getInstance();
    }

    ######################################################################################################################
    ######################################## INDICADORES DE VINCULACION ##################################################  
    ######################################################################################################################  

    /**
    *Recupera el calendario de actividades de promocion
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function calendarioActividadesPromocionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $calendarios=Calendario::join('tipo_calendario','calendario.tipo_calendario_id','=','tipo_calendario.id')
        ->where('tipo_calendario.id',2)
        ->orderBy('calendario.fecha_inicio', 'asc')
        ->get(['calendario.titulo as titulo','calendario.descripcion as descripcion','calendario.fecha_inicio as fecha_inicio','calendario.fecha_final as fecha_final','calendario.id', 'calendario.fecha_mostrar']);


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

      $this->render('indicadoresvinculacion-calendario-actividades-promocion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar ,'calendarios' => $calendarios));

    }

    /**
    *Exporta el excel del calendario de actividades de promocion
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelCalendarioActividadesPromocionAction()
    {
      $datos=Calendario::join('tipo_calendario','calendario.tipo_calendario_id','=','tipo_calendario.id')
        ->where('tipo_calendario.id',2)
        ->orderBy('calendario.fecha_inicio', 'asc')
        ->get(['calendario.titulo as titulo','calendario.descripcion as descripcion','calendario.fecha_inicio as fecha_inicio','calendario.fecha_final as fecha_final','calendario.id', 'calendario.fecha_mostrar']);

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Calendario');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':D'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->titulo);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->descripcion);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->fecha_mostrar);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Título");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Descripción");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Fecha");
              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Calendario de promocion.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera la base de datos de los Ex-Alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function bdExAlumnosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=ExAlumnos::join('persona','ex_alumnos.persona_id','=','persona.id')
      ->orderBy('anio_egreso', 'asc')
      ->get(['ex_alumnos.anio_egreso as anio','persona.name as nombre','persona.last_name as apellido','persona.email as mail','persona.phone as telefono']);

      $this->render('indicadoresvinculacion-bd-ex-alumnos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar ,'datos' => $datos));

    }

     /**
    *Exporta el excel a base de datos de los Ex-Alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelbdExAlumnosAction()
    {
      $datos=ExAlumnos::join('persona','ex_alumnos.persona_id','=','persona.id')
      ->orderBy('anio_egreso', 'asc')
      ->get(['ex_alumnos.anio_egreso as anio','persona.name as nombre','persona.last_name as apellido','persona.email as mail','persona.phone as telefono']);

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('ExAlumnos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->nombre);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->mail);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->telefono);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Nombre");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Apellidos");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Email");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Telefono");
              $cells = 'B'.$rowCount.':F'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="ExAlumnos.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera el area de desempeño de los Ex-Alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function areaDesempenioExAlumnosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=AreaDesempenio::join('ex_alumnos','area_desempenio.id','=','ex_alumnos.area_desempenio_id')
      ->join('persona','ex_alumnos.persona_id','=','persona.id')
      ->orderBy('area_desempenio.nombre','asc')
      ->get(['area_desempenio.nombre as area','persona.name as nombre','persona.last_name as apellido','persona.run as rut']); 


      $this->render('indicadoresvinculacion-area-desempenio-ex-alumnos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos'=> $datos));

    }

     /**
    *Exporta el excel a base de datos de los Ex-Alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelAreaDesempenioExAlumnosAction()
    {
      $datos=AreaDesempenio::join('ex_alumnos','area_desempenio.id','=','ex_alumnos.area_desempenio_id')
      ->join('persona','ex_alumnos.persona_id','=','persona.id')
      ->orderBy('area_desempenio.nombre','asc')
      ->get(['area_desempenio.nombre as area','persona.name as nombre','persona.last_name as apellido','persona.run as rut']);

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('AreaDesempenioExAlumnos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':D'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->rut);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->nombre.' '.$dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->area);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Rut");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Nombre");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Área de Desempeño");
              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Area de Desempeno ExAlumnos.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    
    /**
    *Recupera los integrantes de centro de ex-alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function centroExAlumnosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=CentroExAlumnos::join('ex_alumnos','ex_alumnos.id','=','centro_exalumnos.ex_alumnos_id')
      ->join('persona','ex_alumnos.persona_id','=','persona.id')
      ->get(['persona.name as nombre','persona.last_name as apellido','persona.run as rut', 'centro_exalumnos.cargo as cargo']); 

      $this->render('indicadoresvinculacion-centro-ex-alumnos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));

    }

    /**
    *Exporta el excel a base de datos de los centro de exalumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelCentroExAlumnosAction()
    {
      $datos=CentroExAlumnos::join('ex_alumnos','ex_alumnos.id','=','centro_exalumnos.ex_alumnos_id')
      ->join('persona','ex_alumnos.persona_id','=','persona.id')
      ->get(['persona.name as nombre','persona.last_name as apellido','persona.run as rut', 'centro_exalumnos.cargo as cargo']); 

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('CentroExAlumnos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':D'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->rut);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->nombre.' '.$dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->cargo);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Rut");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Nombre");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Cargo");
              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Centro de ExAlumnos.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera las actividades de los ex-alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function actividadesExAlumnosAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

       $calendarios=Calendario::join('tipo_calendario','calendario.tipo_calendario_id','=','tipo_calendario.id')
        ->where('tipo_calendario.id',3)
        ->orderBy('calendario.fecha_inicio', 'asc')
        ->get(['calendario.titulo as titulo','calendario.descripcion as descripcion','calendario.fecha_inicio as fecha_inicio','calendario.fecha_final as fecha_final','calendario.id',  'calendario.fecha_mostrar']); 

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

      $this->render('indicadoresvinculacion-actividades-ex-alumnos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'calendarios'=> $calendarios));

    }

    /**
    *Exporta el excel de las actividades Ex Alumnos
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelActividadesExAlumnosAction()
    {
      $datos=Calendario::join('tipo_calendario','calendario.tipo_calendario_id','=','tipo_calendario.id')
        ->where('tipo_calendario.id',3)
        ->orderBy('calendario.fecha_inicio', 'asc')
        ->get(['calendario.titulo as titulo','calendario.descripcion as descripcion','calendario.fecha_inicio as fecha_inicio','calendario.fecha_final as fecha_final','calendario.id','calendario.fecha_mostrar']); 

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('ActividadExAlumnos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':D'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->titulo);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->descripcion);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->fecha_mostrar);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Título");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Descripción");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Periodo");
              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Actividades ExAlumnos.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera la base de datos de los empleadores
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function bdEmpleadoresAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Empresa::orderBy('nombre', 'asc')->get();

      $this->render('indicadoresvinculacion-bd-empleadores',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos'=> $datos));

    }

    /**
    *Exporta el excel de las empresas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelBdEmpleadoresAction()
    {
      $datos=Empresa::orderBy('nombre', 'asc')->get(); 

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Empresas');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->nombre);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->contacto);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->cargo);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->mail);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->fono);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Empresa");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Persona Contacto");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Cargo");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Mail");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Teléfono");
              $cells = 'B'.$rowCount.':F'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Base de datos Empleadores.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera los convenios vigentes
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function conveniosVigentesAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $this->render('indicadoresvinculacion-convenios-vigentes',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));

    }

    /**
    *Recupera las ofertas de las practicas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function ofertasPracticaAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

       $datos=Practicas::join('empresa','practicas.empresa_id','=','empresa.id')
      ->orderBy('empresa.nombre','asc')
      ->get(['practicas.periodo as periodo','practicas.requisitos as requisitos','practicas.contacto as contacto','empresa.nombre as empresa']); 

      $this->render('indicadoresvinculacion-ofertas-practica',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar,'datos'=>$datos));

    }

    /**
    *Exporta el excel de las ofertas de las practicas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelOfertasPracticaAction()
    {
      $datos=Practicas::join('empresa','practicas.empresa_id','=','empresa.id')
      ->orderBy('empresa.nombre','asc')
      ->get(['practicas.periodo as periodo','practicas.requisitos as requisitos','practicas.contacto as contacto','empresa.nombre as empresa']); 

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('OfertasPracticas');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':D'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->empresa);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->periodo);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->contacto);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Empresa");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Periodo");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Persona Contacto");
              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Ofertas de Practicas.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 

    /**
    *Recupera las memorias realizadas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function memoriasRealizadasAction(){

      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Memoria::join('empresa','memoria.empresa_id','=','empresa.id')
      ->join('plan','memoria.plan_id','=','plan.id')
      ->join('alumno','memoria.alumno_id', '=', 'alumno.id')
      ->join('profesor','memoria.profesor_id','=','profesor.id')
      ->orderBy('memoria.anio','asc')
      ->get(['memoria.anio as anio','alumno.id as alumno_id','profesor.id as profesor_id','memoria.tema as tema', 'plan.nombre as plan','empresa.nombre as empresa']); 

      $data = [];

      foreach ($datos as $dato)
      {

        $alumno_id=Alumno::where('alumno.id', $dato->alumno_id)->first();
        $profesor_id=Profesor::where('profesor.id', $dato->profesor_id)->first();

        $alumno=Person::where('persona.id', $alumno_id->persona_id)->first();
        $profesor=Person::where('persona.id', $profesor_id->persona_id)->first();

        $data2 = array('tema' => $dato->tema, 'anio' => $dato->anio, 'empresa' => $dato->empresa, 'plan' => $dato->plan, 'nombre' => $alumno->name, 'apellido' => $alumno->last_name, 'profesor' => $profesor->name );
        array_push($data, $data2);   
      }   

        $datos = json_decode (json_encode ($data), FALSE);

      $this->render('indicadoresvinculacion-memorias-realizadas',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));

    }

    /**
    *Exporta el excel de las memorias realizadas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelMemoriasRealizadasAction()
    {
      $datos=Memoria::join('empresa','memoria.empresa_id','=','empresa.id')
      ->join('plan','memoria.plan_id','=','plan.id')
      ->join('alumno','memoria.alumno_id', '=', 'alumno.id')
      ->join('profesor','memoria.profesor_id','=','profesor.id')
      ->orderBy('memoria.anio','asc')
      ->get(['memoria.anio as anio','alumno.id as alumno_id','profesor.id as profesor_id','memoria.tema as tema', 'plan.nombre as plan','empresa.nombre as empresa']); 

      $data = [];

      foreach ($datos as $dato)
      {

        $alumno_id=Alumno::where('alumno.id', $dato->alumno_id)->first();
        $profesor_id=Profesor::where('profesor.id', $dato->profesor_id)->first();

        $alumno=Person::where('persona.id', $alumno_id->persona_id)->first();
        $profesor=Person::where('persona.id', $profesor_id->persona_id)->first();

        $data2 = array('tema' => $dato->tema, 'anio' => $dato->anio, 'empresa' => $dato->empresa, 'plan' => $dato->plan, 'nombre' => $alumno->name, 'apellido' => $alumno->last_name, 'profesor' => $profesor->name );
        array_push($data, $data2);   
      }   

        $datos = json_decode (json_encode ($data), FALSE);

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Memorias');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'B'.$rowCount.':H'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->nombre);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->tema);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->profesor);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $dato->empresa);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $dato->plan);
                
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Fecha");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Nombre");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Apellido");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Tema");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Profesor Guía");
              $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, "Empresa");
              $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, "Título al que opta");

              $cells = 'B'.$rowCount.':D'.$rowCount;
              $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true);
              $row++;
              $rowCount++; 
          }
      }

      // Setear el largo de las hojas del excel
      for($col = 'A'; $col !== 'Z'; $col++) {
        $objPHPExcel->getActiveSheet()
        ->getColumnDimension($col)
        ->setAutoSize(true);
      }

      // Crea el archivo
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Memorias Realizadas.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 
}

?>