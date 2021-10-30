<?php

namespace SGI\Controller;
use SGI\Models\Department;
use SGI\Models\Congress;
use SGI\Models\Publication;
use SGI\Models\Project;
use SGI\Models\Thesis;
use SGI\Models\Degree;
use SGI\Models\Person;
use SGI\Models\EnglishProgram;
use SGI\Models\PersonPublication;
use SGI\Models\PersonProject;
use SGI\Models\PersonScholarship;
use SGI\Models\PersonCongress;
use SGI\Models\PersonThesis;
use SGI\Models\PersonPuente;
use SGI\Models\PersonStudiesProgram;
use SGI\Models\Piic;
use SGI\Models\Profesor;
use SGI\Models\Program;
use SGI\Models\ProgramType;
use SGI\Models\Patent;
use SGI\Models\Journal;
use SGI\Models\JournalArea;
use Illuminate\Database\Capsule\Manager as DB;

use SGI\Models\Proyecto;
use SGI\Models\Publicacion;

use SGI\Models\ImpactFactor;

class Api extends \SlimController\SlimController
{
 

  /********************API Temporal**************************************/
  /**
 	  * Reemplaza los archivos json estáticos los colores de los departamentos. Busca en
 	  * la caché si existe el json y en caso contrario recupera los
 	  * datos y lo crea.
 	  * Usado principalmente por las visualizaciones de los gŕaficos. Se puede
 	  * cambiar en el administrador.
 	  *
 	  * @return un json que tiene todos los colores de la forma
 	  * [{"name": "Departamento de mecánica", "color":"#ffffff"}, ...]
 	  */
	public function departamentosColorAction(){
		$driver = new \Stash\Driver\FileSystem();
		$driver->setOptions(array('path' => dirname(__FILE__) . '/../share/cache/queries/'));
		$pool = new \Stash\Pool($driver);
		$item = $pool->getItem('listadodepartamentoscolorjson');
		$depas = $item->get();
		$i = 0;
		if($item->isMiss()){
			$depa_tmp = array();
			$department = new Department();
			$datos = $department->select('name', 'color')->get();
			foreach($datos as $dato){
				$depa_tmp[$i]["name"] = $dato->name;
				$depa_tmp[$i]["color"] = "#".$dato->color;
				$i++;
			}

			$depa_tmp = utf8_encode((string)json_encode($depa_tmp));
			$item->set($depa_tmp);
			$depas = $depa_tmp;
 	 	}
 	 	echo $depas;
	}


 	 /**
 	  * Reemplaza los archivos json estáticos de las personas. Busca en
 	  * la caché si existe el json y en caso contrario recupera los
 	  * datos y lo crea.
 	  *
 	  * @return un json que tiene todas las personas de la forma
 	  * [{"id":"1", "name": "juan perez"}, ...]
 	  */

 	public function personasAction(){
		$driver = new \Stash\Driver\FileSystem();
		$driver->setOptions(array('path' => dirname(__FILE__) . '/../share/cache/queries/'));
		$pool = new \Stash\Pool($driver);
		$item = $pool->getItem('listadopersonasjson');
		$personas = $item->get();
		$personas_array = array();
		$i = 0;
		if($item->isMiss()){
			$person = new Person();
			$datos = $person->select('id', 'name', 'last_name', 'm_name')->get();
			foreach($datos as $dato){
				$personas_array[$i]["id"] = $dato->id;
				$personas_array[$i]["name"] = $dato->name. ' '. $dato->last_name. ' ' . $dato->m_name;
				$i++;
			}
			$personas_array = utf8_encode((string)json_encode($personas_array));
			$item->set($personas_array);
 	 	}
 	 	echo $personas;
	}

	//Modificado para DIMM
	public function getPublicacionesAction(){
		$id = $_GET["id"];
		$anios = array();
		$anios_project = array();
		$tipos_pub = array();
		$tipos_proyecto = array();
		$termino = date('Y');
		for($i = 2007; $i <= $termino ; $i++){
			$anios[$i] = 0;
			$anios_project[$i] = 0;
		}
		$publicaciones=Publicacion::where('profesor_id',$id)->orderBy('anio', 'asc')->get();
		foreach($publicaciones as $pub){
			$anio = Publicacion::where('id',$pub->id)->select('anio')->first();
			if(isset($anios[$anio->anio]))
				$anios[$anio->anio]++;
		}
		$retorno = array();

		$proyectos=Proyecto::where('Profesor_id',$id)->get();
		foreach($anios as $anio){
			$retorno[] = $anio;
		}
		$proyectos= Proyecto::where('profesor_id',$id)->orderBy('anio_inicio', 'asc')->get();
		foreach($proyectos as $p){
			$anio =  Proyecto::where('id',$p->id)->select('anio_inicio')->first();
			if(isset($anios_project[$anio->anio_inicio]))
				$anios_project[$anio->anio_inicio]++;
		}
		$retorno_proyectos = array();
		foreach($anios_project as $anio){
			$retorno_proyectos[] = $anio;
		}

		echo json_encode(array('pubs' => $retorno, 'proyectos' => $retorno_proyectos));
	}

	public function getProyectosPersonaAction(){
		$id = $_GET["id"];
		$project = new Project();
		$person_proy = new PersonProject();
		$retorno = array();
		$i = 0;
		$res = 2;
		$proyectos = $person_proy->join('project', 'project.id', '=', 'person_has_project.project_id')->where('person_has_project.person_id', $id)->select('person_has_project.project_id')->orderBy('project.year', 'desc')->get();
		//$coautores = $person_pub->where('publication_id', $id)->where("is_ca", 0)->select('person_id')->get();
		foreach($proyectos as $proy){
			$p = $project->where('id', $proy->project_id)->first();
			$retorno[$i]["id"] = $p->id;
			$retorno[$i]["title"] = $p->title;
			$retorno[$i]["type"] = ucfirst($p->type);
			$retorno[$i]["participation"] = ucfirst($p->participation);
			$retorno[$i]["year"] = $p->year;
			$i++;
			$res = 1;
		}

		echo json_encode(array("data" => $retorno, "res" => $res));
	}


  public function getCongresosPersonaAction(){
		$id = $_GET["id"];
		$project = new Congress();
		$person_proy = new PersonCongress();
		$retorno = array();
		$i = 0;
		$res = 2;
		//$proyectos = $person_proy->where('person_id', $id)->select('congress_id')->orderBy('congress_id', 'asc')->get();
    $proyectos = $project->join('person_has_congress', 'person_has_congress.congress_id', '=', 'congress.id')
    ->where('person_has_congress.person_id', $id)->select('person_has_congress.congress_id')->orderBy('congress.year', 'desc')->get();
    //var_dump($proyectos);
		//$coautores = $person_pub->where('publication_id', $id)->where("is_ca", 0)->select('person_id')->get();
		foreach($proyectos as $proy){
			$p = $project->where('id', $proy->congress_id)->first();
			$retorno[$i]["id"] = $p->id;
			$retorno[$i]["title"] = $p->title;
			$retorno[$i]["congress"] = ucfirst($p->congress);
			$retorno[$i]["country"] = ucfirst($p->country);
			$retorno[$i]["year"] = $p->year;
      $retorno[$i]["city"] = $p->city;
			$i++;
			$res = 1;
		}

		echo json_encode(array("data" => $retorno, "res" => $res));
	}


  public function getTesisPersonaAction(){
		$id = $_GET["id"];
		$project = new Thesis();
		$person_proy = new PersonThesis();
		$retorno = array();
		$i = 0;
		$res = 2;
		$proyectos = $person_proy->join('thesis', 'thesis.id', '=', 'person_has_thesis.thesis_id')->where('person_id', $id)->select('thesis_id')->orderBy('thesis.year', 'desc')->get();
		//$coautores = $person_pub->where('publication_id', $id)->where("is_ca", 0)->select('person_id')->get();
		foreach($proyectos as $proy){
			$p = $project->where('id', $proy->thesis_id)->first();
			$retorno[$i]["id"] = $p->id;
			$retorno[$i]["title"] = $p->title;
			$retorno[$i]["type"] = ucfirst($p->type);
			$retorno[$i]["student_name"] = ucfirst($p->student_name);
			$retorno[$i]["year"] = $p->year;
			$i++;
			$res = 1;
		}

		echo json_encode(array("data" => $retorno, "res" => $res));
	}


  public function getPublicacionesPersonaAction(){
		$id = $_GET["id"];
		$publication = new Publication();
		$person_pub = new PersonPublication();
		$retorno = array();
		$i = 0;
    $publicaciones = $person_pub->join('publication','publication.id','=','person_has_publication.publication_id')->where('person_has_publication.person_id', $id)
    ->orderBy('publication.year', 'desc')->select('person_has_publication.publication_id')->get();
		//$coautores = $person_pub->where('publication_id', $id)->where("is_ca", 0)->select('person_id')->get();
		foreach($publicaciones as $pub){
			$p = $publication->where('id', $pub->publication_id)->first();
			$retorno[$i]["id"] = $p->id;
			$retorno[$i]["title"] = $p->title;
			$retorno[$i]["year"] = $p->year;
			$retorno[$i]["other_author"] = $p->other_author;
			$i++;
		}
		echo json_encode($retorno);
	}

	public function saveCanvasAction(){
		$data = $_POST['data'];
		$data = substr($data,strpos($data,",")+1);
		$data = base64_decode($data);
		$file = './img/grafico.png';
		file_put_contents($file, $data);
		echo "Success!";
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	public function checkRutProfesorAction(){
		$respuesta = array(
			'estado' => '',
			'persona' => '',
			'rut_recibido' => '',
			'dv_recibido' => ''
		);

		if(count(explode('-', $_GET['rut'])) != 2){
			$respuesta['estado'] = 'Invalido';
		}else{
			$rut = str_replace('.', '', explode('-', $_GET['rut'])[0]);
			$dv = explode('-', $_GET['rut'])[1];
			if($rut <= 1000 || $rut >= 50000000){
				$respuesta['estado'] = 'Invalido';				
			}else{
				$r=$rut;
				$s=1;
				for($m=0;$r!=0;$r/=10){
					$s=($s+$r%10*(9-$m++%6))%11;
				}
				if(strtoupper($dv) != chr($s?$s+47:75)){
					$respuesta['estado'] = 'Invalido';
				}else{

					$persona = Person::where('run', $rut)->first();
					$respuesta['rut_recibido'] = $rut;
					$respuesta['dv_recibido'] = strtoupper($dv);
					if($persona != NULL){
						// persona ya existe
						$profesor = Profesor::where('persona_id', $persona->id)->first();
						if($profesor != NULL){
							$respuesta['estado'] = "Profesor";
							$respuesta['persona'] = $persona;
						}else{
							$respuesta['estado'] = "Persona";
							$respuesta['persona'] = $persona;
							$respuesta['persona']['run'] = number_format($respuesta['persona']['run'], 0, ',', '.');
							$respuesta['persona']['birthday'] = date_format(date_create($respuesta['persona']['birthday']),"d-m-Y");
						}
					}else{
						$respuesta['estado'] = "Nuevo";
					}
				}
			}
		}
  	echo json_encode($respuesta);
	}
}
