<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;

use SGI\Models\Acreditacion;
use SGI\Models\Instalacion;
use SGI\Models\Inversion;

use PHPExcel;

use Illuminate\Database\Capsule\Manager as DB;

class IndicadoresGestion extends \SlimController\SlimController
{

    protected $app;
    function __construct() {
        $this->app = Slim::getInstance();
    }

    #######################################################################################################################
    ##############################################   ESTADO DE ACREDITACION   #############################################
    #######################################################################################################################

    /**
    *Recupera el estado de acreditacion de la carrera ingenieria civil Metalurgia
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function listarEstadoAcreditacionAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Acreditacion::join('agencia','agencia.id','=','acreditacion.agencia_id')
      ->join('plan','plan.id','=','acreditacion.plan_id')
      ->get(['plan.nombre as plan','acreditacion.duracion',DB::raw("DATE_FORMAT(acreditacion.anio_inicio, '%Y') as inicio"),DB::raw("DATE_FORMAT(acreditacion.anio_fin, '%Y') as fin"),'agencia.nombre as agencia','acreditacion.acta']); 

      $this->render('indicadoresgestion-estado-acreditacion',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel del estado de acreditacion de la carrera ingenieria civil Metalurgia
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelEstadoAcreditacionAction()
    {

     // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="Estado de Acreditacion.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Acreditacion::join('agencia','agencia.id','=','acreditacion.agencia_id')
      ->join('plan','plan.id','=','acreditacion.plan_id')
      ->get(['plan.nombre as plan','acreditacion.duracion',DB::raw("DATE_FORMAT(acreditacion.anio_inicio, '%Y') as inicio"),DB::raw("DATE_FORMAT(acreditacion.anio_fin, '%Y') as fin"),'agencia.nombre as agencia','acreditacion.acta']); 

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('EstadoAcreditacion');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

        while($row <= $tamaño)
        {

          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':G'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->plan);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->duracion);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->inicio.'-'.$dato->fin);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $dato->agencia);
                $row++;
                $rowCount++;        
            
            }
           
        }else
        {

              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Plan");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Duración");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Periodo");
              $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, "Agencia");
              $cells = 'D'.$rowCount.':G'.$rowCount;
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


      /*
      // Configura los datos de los ejes del grafico
     
      $inicio = 3;
      $final = $inicio + 2;

      $agency = array(
        new \PHPExcel_Chart_DataSeriesValues('String', 'EstadoAcreditacion!$E$'.$inicio.':$E$'.$final.''),
      );

      $period = array(
        new \PHPExcel_Chart_DataSeriesValues('Number', 'EstadoAcreditacion!$G$'.$inicio.':$G$'.$final.''),
      );

    
      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_PIECHART,       // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_STANDARD,  // plotGrouping
        range(0, count($agency)-1),                                        // plotOrder
        array(),                                        // plotLabel
        $period,                             // plotCategory
        $agency                            // plotValues
      );

      $plotarea = new \PHPExcel_Chart_PlotArea(null, array($series));
      
      $title = new \PHPExcel_Chart_Title('Pie chart');
      $legend = new \PHPExcel_Chart_Legend(\PHPExcel_Chart_Legend::POSITION_RIGHT, null, false);

      $chart = new \PHPExcel_Chart(
        'chart2',                                       // name
         $title,                                         // title
        $legend,                                        // legend
        $plotarea,                                      // plotArea
        true,                                           // plotVisibleOnly
        0,                                              // displayBlanksAs
        null,                                           // xAxisLabel
        null                                            // yAxisLabel
      );

      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      */

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    }    
    /**
    *Recupera los datos de acreditacion mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarAcreditacionAction(){

      $datos=Acreditacion::join('agencia','agencia.id','=','acreditacion.agencia_id')
      ->join('plan','plan.id','=','acreditacion.plan_id')
      ->get(['plan.nombre as plan','acreditacion.duracion',DB::raw("DATE_FORMAT(acreditacion.anio_inicio, '%Y') as inicio"),DB::raw("DATE_FORMAT(acreditacion.anio_fin, '%Y') as fin"),'agencia.nombre as agencia','acreditacion.acta']); 

      $response = [];
      $color = ['#034f84','#cc5f00','#80ced6' ];
      $i=0;

      foreach ($datos as $dato) {
           $response2 = array('color' => $color[$i], 'value' => $dato->duracion, 'label' => $dato->agencia);
           array_push($response, $response2);
           $i++; 
      }
      $response = json_encode($response);
      echo $response;
    }

    #######################################################################################################################
    ########################################   M2 INSTALACIONES ACADEMICAS    #############################################
    #######################################################################################################################

    /**
    *Recupera los metros cuadrados de las instalaciones academicas de la Casa Central 
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */ 

    public function listarM2AcademicosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id','!=',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']); 

      $this->render('indicadoresgestion-m2-academicos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de los metros cuadrados de las instalaciones academicas de la Casa Central
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarM2AcademicosAction()
    {

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="M2 Instalaciones Academicos.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id','!=',2)
      ->orderBy('tipo_instalacion.nombre', 'ASC')
      ->orderBy('instalacion.anio', 'ASC')
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']); 

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('M2InstalacionesAcademicas');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $row = 1 ;

        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':G'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->metros_cuadrados);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $dato->tipo);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Metros Cuadrados");
              $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, "Tipo de instalación");
              $cells = 'D'.$rowCount.':G'.$rowCount;
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

      // Configura los datos de los ejes del grafico
     
      $inicio = 3;
      $final = $inicio + 2;

      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'M2InstalacionesAcademicas!$D$'.$inicio.':$D$'.$final.'');
      $doc = new \PHPExcel_Chart_DataSeriesValues('String', 'M2InstalacionesAcademicas!$F$'.$inicio.':$F$'.$final.'');

      $inicio = $final + 1;
      $final = $inicio + 2;
      $lab = new \PHPExcel_Chart_DataSeriesValues('String', 'M2InstalacionesAcademicas!$F$'.$inicio.':$F$'.$final.'');

      $m2 = array(
            $doc,
            $lab,
      );

      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,        // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,   // plotGrouping
        range(0, count($m2)-1),                           // plotOrder
        array(),                                          // plotLabel
        array($year),                                     // plotCategory
        $m2                                               // plotValues
      );

      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Metros Cuadrados');
      $title = new \PHPExcel_Chart_Title('Instalaciones Academicas');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    }    

    /**
    *Recupera los metros cuadrados de las instalaciones de laboratorios academicas de la Casa Central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarM2InstalacionesAcademicasLabAction(){

  
     
      $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id','!=',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->tipo;
          if ($tipo == "Laboratorios"){
            $response2 = array('anio' => $dato->anio, 'm2' => $dato->metros_cuadrados);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

    /**
    *Recupera los metros cuadrados de las instalaciones de docencia academicas de la Casa Central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarM2InstalacionesAcademicasDocAction(){
     
      $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id','!=',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->tipo;
          if ($tipo == "Docentes"){
            $response2 = array('anio' => $dato->anio, 'm2' => $dato->metros_cuadrados);
            array_push($response, $response2);
          }
      }
      $response = json_encode($response);
      echo $response;
    }

    #######################################################################################################################
    #######################################   M2 INSTALACIONES ADMINISTRATIVOS  ###########################################
    #######################################################################################################################

    /**
    *Recupera los metros cuadrados de las instalaciones administrativas de la Casa Central 
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */ 

    public function listarM2AdministrativosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

       $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']); 

      $this->render('indicadoresgestion-m2-administrativos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de los metros cuadrados de las instalaciones administrativas de la Casa Central 
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarM2AdministrativosAction()
    {

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="M2 Instalaciones Administrativos.xlsx"');
      header('Cache-Control: max-age=0');

       $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']);

    

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('M2InstalacionesAdministrativos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $final = count($datos);
      $row = 1 ;

        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':G'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->metros_cuadrados);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $dato->tipo);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Metros Cuadrados");
              $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, "Tipo de instalación");
              $cells = 'D'.$rowCount.':G'.$rowCount;
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

      $inicio = 3;
      $final = $tamaño + 2;

      // Confifura los datos de los ejes del gradico
      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'M2InstalacionesAdministrativos!$D$'.$inicio.':$D$'.$final.'');
      $m2 = new \PHPExcel_Chart_DataSeriesValues('String', 'M2InstalacionesAdministrativos!$F$'.$inicio.':$F$'.$final.'');
      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,        // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,   // plotGrouping
        array(0),                                         // plotOrder
        array(),                                          // plotLabel
        array($year),                                     // plotCategory
        array($m2)                                         // plotValues
      );


      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Metros Cuadrados');
      $title = new \PHPExcel_Chart_Title('Instalaciones Administrativas');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    }  

    public function buscarM2AdministrativosAction(){

       $datos=Instalacion::join('tipo_instalacion','tipo_instalacion.id','=','instalacion.tipo_instalacion_id')
      ->join('campus','instalacion.campus_id','=','campus.id')
      ->where('tipo_instalacion.id',2)
      ->get(['instalacion.anio','campus.nombre as campus','instalacion.metros_cuadrados','tipo_instalacion.nombre as tipo']);

      $response = [];

      foreach ($datos as $dato) {
            $response2 = array('anio' => $dato->anio, 'm2' => $dato->metros_cuadrados);
            array_push($response, $response2);
      }

      $response = json_encode($response);
      echo $response;
    }

    #######################################################################################################################
    ##############################################   INVERSION INFRAESTRUCTURA  ###########################################
    #######################################################################################################################

    /**
    *Recupera la inversion en infraestructura de las instalaciones en los diferentes años
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */ 
    
    public function listarInversionInfraestructuraAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',1)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      foreach ($datos as $dato) {
        $dato->inversion=number_format($dato->inversion,0,'','.');
      }

      $this->render('indicadoresgestion-inversion-infraestructura',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de la inversion en infraestructura de las instalaciones en los diferentes años
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarInversionInfraestructuraAction()
    {

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Inversion Infraestructura.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',1)
      ->orderBy('campus.nombre', 'DESC')
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']);

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('InversionInfraestructura');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $row = 1 ;

        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->inversion);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Inversión");
              $cells = 'D'.$rowCount.':F'.$rowCount;
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

      // Configura los datos de los ejes del grafico
     
      $inicio = 3;
      $final = $inicio + 3;

      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'InversionInfraestructura!$D$'.$inicio.':$D$'.$final.'');
      $cc = new \PHPExcel_Chart_DataSeriesValues('Number', 'InversionInfraestructura!$F$'.$inicio.':$F$'.$final.'');

      $inicio = $final + 1;
      $final = $inicio + 1;
      $csj = new \PHPExcel_Chart_DataSeriesValues('Number', 'InversionInfraestructura!$F$'.$inicio.':$F$'.$final.'');

      $inv = array(
            $cc,
            $csj,
      );

      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,       // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
        range(0, count($inv)-1),                                       // plotOrder
        array(),                                        // plotLabel
        array($year),                             // plotCategory
        $inv                                 // plotValues
      );

      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Inversión');
      $title = new \PHPExcel_Chart_Title('Inversion Infraestructura');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    } 

    /**
    *Recupera la inversion en infraestructura de las instalaciones en los diferentes años en la Casa Central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 
    
    public function buscarInversionInfraestructuraCCAction(){
      

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',1)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Casa Central"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);
          }
      }
      $response = json_encode($response);
      echo $response;
    }

    /**
    *Recupera la inversion en infraestructura de las instalaciones en los diferentes años en el campus San Joaquin mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 
    
    public function buscarInversionInfraestructuraCSJAction(){
      

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',1)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Campus San Joaquín"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

    #######################################################################################################################
    ##############################################   INVERSION EQUIPAMIENTO  ##############################################
    #######################################################################################################################

    /**
    *Recupera la inversion en equipamiento en los diferentes años
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */  

    public function listarInversionEquipamientoAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',2)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      foreach ($datos as $dato) {
        $dato->inversion=number_format($dato->inversion,0,'','.');
      }

      $this->render('indicadoresgestion-inversion-equipamiento',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de la inversion en equipamiento en los diferentes años
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarInversionEquipamientoAction()
    {

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Inversion Equipamiento.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',2)
      ->orderBy('campus.nombre', 'desc')
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

  
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('InversionEquipamiento');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $row = 1 ;

        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->inversion);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Inversión");
              $cells = 'D'.$rowCount.':F'.$rowCount;
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

      // Configura los datos de los ejes del grafico
     
      $inicio = 3;
      $final = $inicio + 4;

      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'InversionEquipamiento!$D$'.$inicio.':$D$'.$final.'');
      $cc = new \PHPExcel_Chart_DataSeriesValues('Number', 'InversionEquipamiento!$F$'.$inicio.':$F$'.$final.'');

      $inicio = $final + 1;
      $final = $inicio + 2;
      $csj = new \PHPExcel_Chart_DataSeriesValues('Number', 'InversionEquipamiento!$F$'.$inicio.':$F$'.$final.'');

      $inv = array(
            $cc,
            $csj,
      );

      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,       // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
        range(0, count($inv)-1),                                       // plotOrder
        array(),                                        // plotLabel
        array($year),                             // plotCategory
        $inv                                // plotValues
      );

      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Inversión');
      $title = new \PHPExcel_Chart_Title('Inversion Equipamiento');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    }

    /**
    *Recupera la inversion en equipamiento en los diferentes años de la Casa Central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */  

    public function buscarInversionEquipamientoCCAction(){
 

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',2)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Casa Central"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

    /**
    *Recupera la inversion en equipamiento en los diferentes años del campus San Joaquin mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */  

    public function buscarInversionEquipamientoCSJAction(){
 

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',2)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Campus San Joaquín"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

    #######################################################################################################################
    ##############################################   DESARROLLO APROBADOS  ################################################
    #######################################################################################################################

    /**
    *Recupera los planes de desarrollo aprobados en los diferentes años
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */ 

    public function listarPlanesDesarrolloAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',3)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      foreach ($datos as $dato) {
        $dato->inversion=number_format($dato->inversion,0,'','.');
      }

      $this->render('indicadoresgestion-planes-desarrollo',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de los planes de desarrollo aprobados en los diferentes años
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarPlanesDesarrolloAction()
    {

      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Planes de Desarrollo Aprobados.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',3)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('PlanesDesarrolloAprobados');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $row = 1 ;

        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->inversion);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Inversión");
              $cells = 'D'.$rowCount.':F'.$rowCount;
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

      $inicio = 3;
      $final = $tamaño + 2;

      // Configura los datos de los ejes del grafico
      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'PlanesDesarrolloAprobados!$D$'.$inicio.':$D$'.$final.'');
      $inv = new \PHPExcel_Chart_DataSeriesValues('Number', 'PlanesDesarrolloAprobados!$F$'.$inicio.':$F$'.$final.'');

      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,       // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
        array(0),                                       // plotOrder
        array(),                                        // plotLabel
        array($year),                             // plotCategory
        array($inv)                                  // plotValues
      );

      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Inversión');
      $title = new \PHPExcel_Chart_Title('Planes de desarrollo aprobados');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    
    }

    /**
    *Recupera los planes de desarrollo aprobados en los diferentes años de la casa central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarPlanesDesarrolloAction(){
      

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->where('tipo_inversion.id',3)
      ->orderBy('inversion.anio','asc')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion']); 

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Casa Central"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }


    #######################################################################################################################
    ##############################################   PRESUPUESTOS OPERACIONES   ###########################################
    #######################################################################################################################

    /**
    *Recupera los presupuestos de las distintas operaciones
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */ 

    public function listarPresupuestoOperacionesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->orderBy('inversion.anio','asc')
      ->groupBy('inversion.anio','campus.nombre')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion',DB::RAW('SUM(inversion.inversion) as inversion')]); 
      
      foreach ($datos as $dato) {
        $dato->inversion=number_format($dato->inversion,0,'','.');
      }

      $this->render('indicadoresgestion-presupuesto-operaciones',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de los presupuestos de las distintas operaciones
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function excelListarPresupuestoOperacionesAction(){
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment;filename="Presupuesto Operaciones.xlsx"');
      header('Cache-Control: max-age=0');

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->orderBy('campus.nombre','desc')
      ->orderBy('inversion.anio','asc')
      ->groupBy('inversion.anio','campus.nombre')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion',DB::RAW('SUM(inversion.inversion) as inversion')]); 
      
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet = $objPHPExcel->getActiveSheet()->setTitle('PresupuestoOperaciones');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      
      $rowCount = 2;
      $tamaño = count($datos);
      $row = 1 ;


        while($row <= $tamaño)
        {
          if ($rowCount % 2 == 0) {
                  $cells = 'D'.$rowCount.':F'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }
            if ($rowCount != 2 ) 
            {
              foreach ($datos as $dato) 
              {

                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->anio);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->campus);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $dato->inversion);
                $row++;
                $rowCount++;        
              }
           
          }
          else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Campus");
              $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, "Inversión");
              $cells = 'D'.$rowCount.':F'.$rowCount;
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

      // Configura los datos de los ejes del grafico
     
      $inicio = 3;
      $final = $inicio + 4;

      $year = new \PHPExcel_Chart_DataSeriesValues('String', 'PresupuestoOperaciones!$D$'.$inicio.':$D$'.$final.'');
      $cc = new \PHPExcel_Chart_DataSeriesValues('Number', 'PresupuestoOperaciones!$F$'.$inicio.':$F$'.$final.'');

      $inicio = $final + 1;
      $final = $inicio + 2;
      $csj = new \PHPExcel_Chart_DataSeriesValues('Number', 'PresupuestoOperaciones!$F$'.$inicio.':$F$'.$final.'');

      $inv = array(
            $cc,
            $csj,
      );


      $series = new \PHPExcel_Chart_DataSeries(
        \PHPExcel_Chart_DataSeries::TYPE_BARCHART,       // plotType
        \PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
        range(0, count($inv)-1),                                       // plotOrder
        array(),                                        // plotLabel
        array($year),                             // plotCategory
        $inv                              // plotValues
      );

      // Configuraciones del grafico
      $series->setPlotDirection(\PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
      $layout = new \PHPExcel_Chart_Layout();
      $plotarea = new \PHPExcel_Chart_PlotArea($layout, array($series));
      $xTitle = new \PHPExcel_Chart_Title('Años');
      $yTitle = new \PHPExcel_Chart_Title('Inversión');
      $title = new \PHPExcel_Chart_Title('Presupuesto Operaciones');
      $chart = new \PHPExcel_Chart('grafico', $title, null, $plotarea, true, 0 ,$xTitle,$yTitle);
      $chart->setTopLeftPosition('J2');
      $chart->setBottomRightPosition('Q16');
      $sheet->addChart($chart);

      // Crea el archivo
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->setIncludeCharts(TRUE);
      ob_end_clean();
      $objWriter->save('php://output');
    }

    /**
    *Recupera los presupuestos de las distintas operaciones en los diferentes años de la casa central mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarPresupuestoOperacionalCCAction(){
      

      $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->orderBy('inversion.anio','asc')
      ->groupBy('inversion.anio','campus.nombre')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion',DB::RAW('SUM(inversion.inversion) as inversion')]); 
      
      $response = [];
      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Casa Central"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

    /**
    *Recupera los presupuestos de las distintas operaciones en los diferentes años del campus san joaquin mediante ajax
    *
    *@author Guillermo Briceño
    *@since 1.0
    */ 

    public function buscarPresupuestoOperacionalCSJAction(){
      
     $datos=Inversion::join('tipo_inversion','tipo_inversion.id','=','inversion.tipo_inversion_id')
      ->join('campus','inversion.campus_id','=','campus.id')
      ->orderBy('inversion.anio','asc')
      ->groupBy('inversion.anio','campus.nombre')
      ->get(['inversion.anio','campus.nombre as campus','inversion.inversion',DB::RAW('SUM(inversion.inversion) as inversion')]); 
      

      $response = [];

      foreach ($datos as $dato) {

          $tipo = $dato->campus;
          if ($tipo == "Campus San Joaquín"){
            $response2 = array('anio' => $dato->anio, 'inversion' => $dato->inversion);
            array_push($response, $response2);

          }
      }
      $response = json_encode($response);
      echo $response;
    }

  public function listarEjecucionPresupuestariaAction(){
    $auth = new Auth();
    if(!$auth->is_valid())
       $this->redirect($this->app->urlFor('Login:index'));
    $user_id = $auth->get_id();
    $nombre_usuario = $auth->get_nombre();
    $nombre_completo = $auth->get_name();
    $avatar = $auth->get_avatar();
    $rol = $auth->get_role();

    $this->render('ejecucion-presupuestaria',
    array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar));
  }
}
?>