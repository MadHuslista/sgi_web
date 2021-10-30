<?php
namespace SGI\Controller;
use SGI\Lib\Auth;
use Slim\Slim;
use SGI\Models\Person;

use SGI\Models\Proyecto;
use SGI\Models\Publicacion;

use PHPExcel;

use Illuminate\Database\Capsule\Manager as DB;

class IndicadoresInvestigacion extends \SlimController\SlimController
{

    protected $app;
    function __construct() {
        $this->app = Slim::getInstance();
    }

    /**
    *Recupera todas las publicaciones realizadas
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function listarPublicacionesAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Publicacion::join('profesor','profesor.id','=','publicacion.profesor_id')
      ->join('persona','persona.id','=','profesor.persona_id')
      ->join('revista','revista.id','=','publicacion.revista_id')
      ->orderBy('publicacion.anio','desc')
      ->get(['publicacion.titulo as nombre','persona.name as academico','persona.last_name as apellido','publicacion.anio as año','publicacion.id']); 

      $this->render('indicadoresinvestigacion-publicaciones',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }

    /**
    *Exporta el excel de todas las publicaciones realizadas
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarPublicacionesAction()
    {

      // Obtencion de los datos
      $datos=Publicacion::join('profesor','profesor.id','=','publicacion.profesor_id')
      ->join('persona','persona.id','=','profesor.persona_id')
      ->join('revista','revista.id','=','publicacion.revista_id')
      ->orderBy('publicacion.anio','desc')
      ->get(['publicacion.titulo as nombre','persona.name as academico','persona.last_name as apellido','publicacion.anio as año','publicacion.id']); 

      // Genera una instancia de PHPExcel 2007

      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Publicaciones');
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

                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->nombre);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->academico.' '.$dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->año);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Titulo");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Autor");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Año");
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
      header('Content-Disposition: attachment;filename="Publicaciones.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    }    

    /**
    *Recupera una publicacion en especifico para ver mas detalle
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function verPublicacionAction($id){
      $auth = new Auth();
        if(!$auth->is_valid())
           $this->redirect($this->app->urlFor('Login:index'));
        $user_id = $auth->get_id();
        $nombre_usuario = $auth->get_nombre();
        $nombre_completo = $auth->get_name();
        $avatar = $auth->get_avatar();
        $rol = $auth->get_role();

        $datos=Publicacion::join('profesor','profesor.id','=','publicacion.profesor_id')
        ->join('persona','persona.id','=','profesor.persona_id')
        ->join('revista','revista.id','=','publicacion.revista_id')
        ->orderBy('publicacion.anio','desc')
        ->where('publicacion.id',$id)
        ->first(['publicacion.titulo as titulo','publicacion.anio','publicacion.volumen','persona.name as nombre','persona.last_name as apellido_p','persona.m_name as apellido_m','revista.nombre as revista','profesor.id as profesor']); 

        $this->render('indicadoresinvestigacion-ver-publicacion',
        array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar,'publicacion' => $datos));
    }

    /**
    *Recupera todos los proyectos realizados
    *
    *@author Diego Bernaldo de Quiros
    *@since 1.0
    */

    public function listarProyectosAction(){
      $auth = new Auth();
      if(!$auth->is_valid())
         $this->redirect($this->app->urlFor('Login:index'));
      $user_id = $auth->get_id();
      $nombre_usuario = $auth->get_nombre();
      $nombre_completo = $auth->get_name();
      $avatar = $auth->get_avatar();
      $rol = $auth->get_role();

      $datos=Proyecto::join('profesor','profesor.id','=','proyecto.profesor_id')
      ->join('persona','persona.id','=','profesor.persona_id')
      ->join('financiamiento','financiamiento.id','=','proyecto.financiamiento_id')
      ->join('linea_investigativa','linea_investigativa.id','=','proyecto.linea_investigativa_id')
      ->get(['proyecto.nombre','linea_investigativa.nombre as linea','persona.name as academico','persona.last_name as apellido','proyecto.anio_inicio as inicio','proyecto.anio_fin as fin','financiamiento.nombre as financiamiento']); 

      $this->render('indicadoresinvestigacion-proyectos',
      array('rol' => $rol,'user_id' => $user_id,'nombre_completo' => $nombre_completo, 'nombre_usuario' => $nombre_usuario, 'avatar' => $avatar, 'datos' => $datos));
    }  

    /**
    *Exporta el excel de todos los proyectos realizados
    *
    *@author Guillermo Briceño
    *@since 1.0
    */

    public function excelListarProyectosAction()
    {
      // Obtencion de los datos
      $datos=Proyecto::join('profesor','profesor.id','=','proyecto.profesor_id')
      ->join('persona','persona.id','=','profesor.persona_id')
      ->join('financiamiento','financiamiento.id','=','proyecto.financiamiento_id')
      ->join('linea_investigativa','linea_investigativa.id','=','proyecto.linea_investigativa_id')
      ->get(['proyecto.nombre','linea_investigativa.nombre as linea','persona.name as academico','persona.last_name as apellido','proyecto.anio_inicio as inicio','proyecto.anio_fin as fin','financiamiento.nombre as financiamiento']); 

      // Genera una instancia de PHPExcel
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->getActiveSheet()->setTitle('Proyectos');
      $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

      $rowCount = 2;
      $tamaño = count($datos);
      $row = 0 ;

      // Llenado de las celdas con los datos
      while($row <= $tamaño)
      {
          if ($rowCount % 2 == 0) {
                  $cells = 'A'.$rowCount.':E'.$rowCount;
                  $objPHPExcel->getActiveSheet()
                      ->getStyle($cells)
                      ->getFill()
                      ->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)
                      ->getStartColor()
                      ->setRGB('B2BD7E');
          }

          if ($rowCount != 2 ) {
            foreach ($datos as $dato) {

                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $dato->nombre);
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $dato->linea);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $dato->academico.' '.$dato->apellido);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $dato->inicio.'-'.$dato->fin);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $dato->financiamiento);
                $row++;
                $rowCount++;        
            } 
          }else
          {
              $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, "Nombre");
              $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, "Línea");
              $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, "Académico Participante");
              $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, "Período");
              $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, "Financiamiento");
              $cells = 'A'.$rowCount.':E'.$rowCount;
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
      header('Content-Disposition: attachment;filename="Listado de Proyectos.xlsx"');
      header('Cache-Control: max-age=0');
      $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save('php://output');

    } 
}
?>