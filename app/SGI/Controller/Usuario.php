<?php

namespace SGI\Controller;
use SGI\Lib\Auth;

use SGI\Models\Person;
use SGI\Models\Institution;
use SGI\Models\Log;
use SGI\Models\Role;
use SGI\Models\Profesor;
use SGI\Models\Publicacion;
use SGI\Models\Proyecto;
use SGI\Models\Alumno;
use SGI\Models\ExAlumnos;
use SGI\Models\LineaInvestigativa;
use SGI\Models\Financiamiento;
use SGI\Models\Revista;
use SGI\Models\ProfesorTesis;
use SGI\Models\Tesis;
use SGI\Models\Congreso;
use SGI\Models\PersonaCongreso;
use SGI\Models\AdministracionAcademica;
use SGI\Models\ExperienciaAcademica;
use SGI\Models\ExperienciaLaboral;
use SGI\Models\DatoExtra;

use Slim\Slim;
use Illuminate\Pagination\Paginator;

use Dompdf\Dompdf;
/**
* Esta clase gestiona los usuarios o personas
*
* @author Guillermo Briceño
* @since 0.1
*/

use Illuminate\Database\Capsule\Manager as DB;
class Usuario extends \SlimController\SlimController
{
	/**
	* Agrega un usuario nuevo
	*
	* @author Guillermo Briceño
	* @since 0.1
	* @todo 
	*/
	public function agregarUsuarioAction(){

		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		if(!$auth->is_role_by_name('admin')){
			Log::insert(['comment' => 'El usuario '.$auth->get_name().' ha intentado editar un usuario sin ser adminstrador', 'date' => date('Y-m-d H:i:s')]);
			$this->redirect($this->app->urlFor('Index:prohibido'));
		}

		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		$app = Slim::getInstance();

		if($app->request->isPost()){
			$params = $app->request->post();
			
			if($_FILES["avatar"]["error"] > 0){
				$rut = explode("-", $params["run"]);
				$pid = Person::insertGetId([
					'name' =>$params["name"],
					'last_name' => $params["last_name"],
					'm_name' => $params["m_name"],
					'run' => $rut[0],
					'dv' => $rut[1],
					'nacionalidad' => $params["nacionalidad"],
					'institucion_id' => $params["institution"],
					'role' => $params["roles"],
					'username' => $params["username"],
					'email' => $params["email"],
					'phone' => $params["phone"],
					'gender' => $params["gender"],
					'birthday' => $params["birthday"]
				]);				
				$this->redirect($this->app->urlFor('Usuario:listarUsuarios'));
			}
			else{
				$storage = new \Upload\Storage\FileSystem('../public/img/personas');
				$file = new \Upload\File('avatar', $storage);
				$new_filename = $params["name"].'.'.$params["last_name"].'.'.$params["m_name"];
				$file->setName($new_filename);
				$file->addValidations(array(
					new \Upload\Validation\Mimetype(array('image/png', 'image/gif', 'image/jpeg', 'image/jpeg')),
					new \Upload\Validation\Size('5M')
				));

				$data = array(
					'name' => $file->getNameWithExtension(),
					'extension' => $file->getExtension(),
					'mime' => $file->getMimetype(),
					'size' => $file->getSize(),
					'md5' => $file->getMd5(),
					'dimensions' => $file->getDimensions()
				);

				try {
					$file->upload();
				} 
				catch (\Exception $e) {
					$errors = $file->getErrors();
				}

				$rut = explode("-", $params["run"]);
				$pid = Person::insertGetId([
					'name' =>$params["name"],
					'last_name' => $params["last_name"],
					'm_name' => $params["m_name"],
					'run' => $rut[0],
					'dv' => $rut[1],
					'nacionalidad' => $params["nacionalidad"],
					'institucion_id' => $params["institution"],
					'username' => $params["username"],
					'email' => $params["email"],
					'phone' => $params["phone"],
					'gender' => $params["gender"],
					'avatar' => $data["name"],
					'role' => $params["roles"],
					'birthday' => $params["birthday"]
				]);
				$this->redirect($this->app->urlFor('Usuario:listarUsuarios'));
			}
		}

		$roles = Role::all();
		$instituciones = Institution::all();
		$this->render('usuarios-agregarusuario', array(
			'rol' => $rol,
			'instituciones'=> $instituciones,
			'roles' => $roles,
			'rol' => $rol,
			$avatar => 'user.png',
			'avatar' => $avatar,
			'user_id' => $user_id,
			'nombre_completo' => $nombre_completo,
			'nombre_usuario' => $nombre_usuario,
			'activado'=> '0')
		);
	}

	/**
	* Edita los datos personales del perfil de un usuario
	*
	* @param int $id El id del usuario a editar
	* @author Guillermo Briceño
	* @since 0.1
	*/

	public function editarUsuarioAction($id){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		$app = Slim::getInstance();

		if($app->request->isPost()){
			$params = $app->request->post();

			if($_FILES["avatar"]["error"] > 0){
				$rut = explode("-", $params["run"]);				
				$dato = Person::where('id', $id)->update([
					'name' =>$params["name"],
					'last_name' => $params["last_name"],
					'm_name' => $params["m_name"],
					'run' => $rut[0],
					'dv' => $rut[1],
					'nacionalidad' => $params["nacionalidad"],
					'institucion_id' => $params["institution"],
					'birthday' => $params["birthday"],
					'username' => $params["username"],
					'email' => $params["email"],
					'phone' => $params["phone"],
					'gender' => $params["gender"]
				]);
				$this->redirect($this->app->urlFor('Usuario:listarUsuarios'));
			}
			else{
				$storage = new \Upload\Storage\FileSystem('../public/img/personas');
				$file = new \Upload\File('avatar', $storage);
	
				$new_filename = $params["name"].'.'.$params["last_name"].'.'.$params["m_name"];
				$file->setName($new_filename);

				$file->addValidations(array(
				new \Upload\Validation\Mimetype(array('image/png', 'image/gif', 'image/jpeg', 'image/jpeg')),
				new \Upload\Validation\Size('5M')
				));
				$data = array(
					'name'			 => $file->getNameWithExtension(),
					'extension'	=> $file->getExtension(),
					'mime'			 => $file->getMimetype(),
					'size'			 => $file->getSize(),
					'md5'				=> $file->getMd5(),
					'dimensions' => $file->getDimensions()
				);

				try {
					$file->upload();
				}
				catch (\Exception $e) {
					$errors = $file->getErrors();
				}

				$rut = explode("-", $params["run"]);
				Person::where('id', $id)->update([
					'name' =>$params["name"],
					'last_name' => $params["last_name"],
					'm_name' => $params["m_name"],
					'run' => $rut[0],
					'dv' => $rut[1],
					'nacionalidad' => $params["nacionalidad"],
					'institucion_id' => $params["institution"],
					'birthday' => date($params["birthday"]),
					'username' => $params["username"],
					'email' => $params["email"],
					'phone' => $params["phone"],
					'gender' => $params["gender"],
					'avatar' => $data["name"]
				]);
				$this->redirect($this->app->urlFor('Usuario:listarUsuarios'));
			}
		}

		$instituciones = Institution::all();
		$persona = Person::where('id', $id)->first();

		$this->render('usuarios-editarusuario', array(
			'rol' => $rol,
			'persona' => $persona,
			'avatar' => $avatar,
			'user_id' => $user_id,
			'user_id' => $user_id,
			'nombre_completo' => $nombre_completo,
			'nombre_usuario' => $nombre_usuario,
			'instituciones' => $instituciones)
		);
	}

	/**
	* Elimina un usuario/persona
	*
	* @author Guillermo Briceño
	* @since 0.1
	*/

	public function eliminarUsuarioAction($id){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$app = Slim::getInstance();
		$rol = $auth->get_role();

		$profesor = Profesor::where('persona_id', $id)->first();

		if ($profesor != null) {
			$profesor->delete();
		}

		$alumno = Alumno::where('persona_id', $id)->first();
		if ($alumno != null) {
			$alumno->delete();
		}
		
		$usuario = Person::find($id);
		$usuario->delete();
		$this->redirect($this->app->urlFor('Usuario:listarUsuarios'));
	}

	/**
	* Lista todos los usuarios/personas
	*
	* @author Guillermo Briceño
	* @since 0.1
	*/

	public function listarUsuariosAction(){

		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$app = Slim::getInstance();
		$rol = $auth->get_role();

		$request = $app->request;
		$current_page = $request->get('page');
		Paginator::currentPageResolver(function() use ($current_page) {
			return $current_page;
		});

		if(null !== $request->get('s') && !empty($request->get('s'))){
			$s = $request->get('s');

			$usuarios = Person::join('role', 'role.id','=','persona.role')
			->where('persona.run','LIKE', $s.'%')
			->whereNotNull('persona.run')
			->orderBy('persona.name', 'ASC')
			->get(['persona.*', 'role.name as role']);
		}
		else{
			$usuarios = Person::whereNotNull('persona.username')
			->join('role', 'role.id','=','persona.role')
			->orderBy('role.name', 'ASC')
			->get(['persona.*', 'role.name as role']);
		}

		$this->render('usuarios-listar-usuarios', array(
			'rol' => $rol,
			'nombre_completo'=>$nombre_completo,
			'nombre_usuario'=>$nombre_usuario,
			'usuarios' => $usuarios,
			'avatar' => $avatar,
			'user_id' => $user_id,
			'request' => $request)
		);
	}

	/**
	* Muestra toda la información de un usuario en particular en lo que respecta
	* a materias académicas
	*
	* @param int $id El id del usuario a revisar
	* @author Guillermo Briceño
	* @since 0.1
	*/

	public function usuarioAction($id){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		//$person	= new Person();
		$persona = Person::select(
			'persona.id as id',
			'persona.username as username',
			'persona.name as name',
			'persona.last_name as last_name',
			'persona.m_name as m_name',
			'persona.run as run',
			'persona.dv as dv',
			'persona.nacionalidad as nacionalidad',
			'persona.gender as gender',
			'persona.birthday as birthday',
			'persona.phone as phone',
			'persona.email as email',
			'persona.avatar as avatar',
			'persona.role as role',
			'institucion.name as institucion')
		->join('institucion','institucion.id','=','persona.institucion_id')
		->where('persona.id', $id)
		->first();
		$alumnos = Alumno::where('persona_id', $id)->get();
		$exalumnos = ExAlumnos::select(
			'ex_alumnos.anio_egreso as anio_egreso',
			'area_desempenio.nombre as area')
		->join('area_desempenio', 'area_desempenio.id', '=', 'ex_alumnos.area_desempenio_id')
		->where('persona_id', $id)
		->get();

		$profesor_ids = Profesor::select('id')->where('persona_id', $id)->get()->toArray();
		$profesores = Profesor::whereIn('id', array_values($profesor_ids))->get();

		// Publicaciones
		$publicaciones = Publicacion::select(
			'publicacion.id as id',
			'publicacion.titulo as titulo',
			'publicacion.anio as anio',
			'publicacion.volumen as volumen',
			'revista.nombre as revista')
		->join('revista', 'revista.id', '=', 'publicacion.revista_id')
		->whereIn('publicacion.profesor_id', array_values($profesor_ids))
		->get();
		$total_publicaciones = count($publicaciones);
		$revistas = Revista::get();
		// Proyectos
		$proyectos = Proyecto::select(
			'proyecto.id as id',
			'proyecto.nombre as nombre',
			'proyecto.anio_inicio as anio_inicio',
			'proyecto.anio_fin as anio_fin',
			'linea_investigativa.nombre as linea_investigativa',
			'financiamiento.nombre as financiamiento')
		->join('linea_investigativa', 'linea_investigativa.id', '=', 'proyecto.linea_investigativa_id')
		->join('financiamiento', 'financiamiento.id', '=', 'proyecto.financiamiento_id')
		->whereIn('proyecto.profesor_id', array_values($profesor_ids))
		->get();
		$total_proyectos = count($proyectos);
		$lineas_investigativas = LineaInvestigativa::get();
		$financiamientos = Financiamiento::get();
		// Tesis
		$tesis = Tesis::select(
			'tesis.id as id',
			'tesis.titulo as titulo',
			'tesis.tipo as tipo',
			'tesis.año as año',
			'tesis.nombre_alumno',
			'profesor_has_tesis.participacion as participacion')
		->join('profesor_has_tesis', 'profesor_has_tesis.tesis_id', '=', 'tesis.id')
		->whereIn('profesor_has_tesis.profesor_id', array_values($profesor_ids))
		->get();
		$total_tesis = count($tesis);
		// Congresos
		$congresos = Congreso::select(
			'congreso.id as id',
			'congreso.nombre as nombre',
			'congreso.titulo as titulo',
			'congreso.año as año',
			DB::raw("DATE_FORMAT(congreso.fecha_inicio, '%d-%m-%Y') as fecha_inicio"),
			DB::raw("DATE_FORMAT(congreso.fecha_termino, '%d-%m-%Y') as fecha_termino"),
			'congreso.ciudad as ciudad',
			'congreso.pais as pais')
		->join('persona_has_congreso', 'persona_has_congreso.congreso_id', '=', 'congreso.id')
		->where('persona_has_congreso.persona_id', $id)
		->get();
		$total_congresos = count($congresos);
		// Experiencia Académica
		$experiencia_academica = ExperienciaAcademica::whereIn('profesor_id', array_values($profesor_ids))
		->orderBy('año_inicio','desc')
		->get();
		// Administración Académica
		$administracion_academica = AdministracionAcademica::select(
			'administracion_academica.id as id',
			'administracion_academica.cargo as cargo',
			'administracion_academica.año_inicio as año_inicio',
			'administracion_academica.año_fin as año_fin',
			'institucion.name as institucion')
		->join('institucion', 'institucion.id', '=', 'administracion_academica.institucion_id')
		->whereIn('profesor_id', array_values($profesor_ids))
		->orderBy('año_inicio','desc')
		->get();
		$instituciones = Institution::orderBy('name','asc')->get();
		// Experiencia Laboral
		$experiencia_laboral = ExperienciaLaboral::where('persona_id', $id)
		->orderBy('año_inicio','desc')
		->get();
		// Otros
		$datos_extra = DatoExtra::where('persona_id', $id)
		->orderBy('id','desc')
		->get();

		$perfil_lineas_investigativas = [];
		foreach ($proyectos as $proyecto) {
			array_push($perfil_lineas_investigativas, $proyecto->linea_investigativa);
		}
		$perfil_lineas_investigativas = array_unique($perfil_lineas_investigativas);

		$this->render('usuarios-mostrar', array(
			'rol' => $rol,
			'id' => $id,
			'persona' => $persona,
			'alumnos' => $alumnos,
			'exalumnos' => $exalumnos,
			'profesores' => $profesores,
			'publicaciones' => $publicaciones,
			'total_publicaciones' => $total_publicaciones,
			'proyectos' => $proyectos,
			'total_proyectos' => $total_proyectos,
			'perfil_lineas_investigativas' => $perfil_lineas_investigativas,
			'lineas_investigativas' => $lineas_investigativas,
			'financiamientos' => $financiamientos,
			'revistas' => $revistas,
			'tesis' => $tesis,
			'total_tesis' => $total_tesis,
			'congresos' => $congresos,
			'total_congresos' => $total_congresos,
			'administracion_academica' => $administracion_academica,
			'instituciones' => $instituciones,
			'experiencia_academica' => $experiencia_academica,
			'experiencia_laboral' => $experiencia_laboral,
			'datos_extra' => $datos_extra,
			'avatar' => $avatar,
			'user_id' => $user_id,
			'nombre_completo' => $nombre_completo,
			'nombre_usuario' => $nombre_usuario)
		);
	}

	public function agregarPublicacionAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			Publicacion::insertGetId([
				'titulo' => $params['titulo'],
				'anio' => $params['año'],
				'volumen' => $params['volumen'],
				'revista_id' => $params['revista_id'],
				'profesor_id' => $profesor->id
			]);
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarPublicacionAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			Publicacion::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarProyectoAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			Proyecto::insertGetId([
				'nombre' => $params['nombre'],
				'anio_inicio' => $params['año_inicio'],
				'anio_fin' => $params['año_fin'],
				'linea_investigativa_id' => $params['linea_id'],
				'financiamiento_id' => $params['finan_id'],
				'profesor_id' => $profesor->id
			]);
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarProyectoAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			Proyecto::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarTesisAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			$tesis_id = Tesis::insertGetId([
				'titulo' => $params['titulo'],
				'nombre_alumno' => $params['nombre_alumno'],
				'tipo' => $params['tipo'],
				'año' => $params['año'],
			]);
			ProfesorTesis::insertGetId([
				'profesor_id' => $profesor->id,
				'tesis_id' => $tesis_id,
				'participacion' => $params['participacion']
			]);
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarTesisAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			Tesis::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarCongresoAction($id){
		$params = Slim::getInstance()->request->post();
		$congreso_id = Congreso::insertGetId([
			'nombre' => $params['nombre'],
			'titulo' => $params['titulo'],
			'año' => date_format(date_create($params['fecha_inicio']),'Y'),
			'fecha_inicio' => date_format(date_create($params['fecha_inicio']),'Y-m-d'),
			'fecha_termino' => date_format(date_create($params['fecha_termino']),'Y-m-d'),
			'ciudad' => $params['ciudad'],
			'pais' => $params['pais']
		]);
		PersonaCongreso::insertGetId([				
			'persona_id' => $id,
			'congreso_id' => $congreso_id
		]);
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarCongresoAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			Congreso::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarAdmAcademicaAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			AdministracionAcademica::insertGetId([
				'institucion_id' => $params['institucion_id'],
				'cargo' => $params['cargo'],
				'año_inicio' => $params['año_inicio'],
				'año_fin' => $params['año_fin'],
				'profesor_id' => $profesor->id
			]);
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarAdmAcademicaAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			AdministracionAcademica::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarExpAcademicaAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){
			$params = Slim::getInstance()->request->post();
			ExperienciaAcademica::insertGetId([
				'institucion' => strtoupper($params['institucion']),
				'departamento' => strtoupper($params['departamento']),
				'año_inicio' => $params['año_inicio'],
				'año_fin' => $params['año_fin'],
				'descripcion' => $params['descripcion'],
				'profesor_id' => $profesor->id
			]);
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarExpAcademicaAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			ExperienciaAcademica::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarExpLaboralAction($id){
		$params = Slim::getInstance()->request->post();
		ExperienciaLaboral::insertGetId([
			'empresa' => $params['empresa'],
			'año_inicio' => $params['año_inicio'],
			'año_fin' => $params['año_fin'],
			'descripcion' => $params['descripcion'],
			'persona_id' => $id
		]);
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarExpLaboralAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			ExperienciaLaboral::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function agregarDatoExtraAction($id){
		$params = Slim::getInstance()->request->post();
		DatoExtra::insertGetId([
			'detalle' => $params['detalle'],
			'persona_id' => $id
		]);
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function eliminarDatoExtraAction($id){
		$profesor = Profesor::where('persona_id', $id)->first();
		if($profesor != NULL){			
			$params = Slim::getInstance()->request->post();
			DatoExtra::where('id', $params['id'])->delete();
		}
		$this->redirect($this->app->urlFor('Usuario:usuario', array('id' => $id)));
	}

	public function listarRevistasAction(){

		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$app = Slim::getInstance();
		$rol = $auth->get_role();

		$request = $app->request;
		$current_page = $request->get('page');
		Paginator::currentPageResolver(function() use ($current_page) {
			return $current_page;
		});

		if(null !== $request->get('s') && !empty($request->get('s'))){
			$s = $request->get('s');

			$revistas = Revista::where('nombre','LIKE', $s.'%')
			->get();
		}
		else{
			$revistas = Revista::select(
				'revista.id as id',
				'revista.nombre as nombre',
				DB::raw('count(publicacion.revista_id) as r_count')
			)
			->leftJoin('publicacion','publicacion.revista_id','=','revista.id')
			->groupBy('revista.id')
			->orderBy('r_count','desc')
			->get();
		}

		$this->render('revistas-listar-revistas', array(
			'rol' => $rol,
			'nombre_completo'=>$nombre_completo,
			'nombre_usuario'=>$nombre_usuario,
			'revistas' => $revistas,
			'avatar' => $avatar,
			'user_id' => $user_id,
			'request' => $request)
		);
	}

	public function agregarRevistaAction(){
		$params = Slim::getInstance()->request->post();
		Revista::insertGetId(['nombre' => $params['nombre']]);
		$this->redirect($this->app->urlFor('Usuario:listarRevistas'));
	}

	public function editarRevistaAction(){
		$params = Slim::getInstance()->request->post();
		Revista::where('id', $params['id'])->update(['nombre' => $params['nombre']]);
		$this->redirect($this->app->urlFor('Usuario:listarRevistas'));
	}

	public function eliminarRevistaAction(){
		$params = Slim::getInstance()->request->post();
		Revista::where('id', $params['id'])->delete();
		$this->redirect($this->app->urlFor('Usuario:listarRevistas'));
	}

	/**
	* Permite ver el registro de logueo del sistema
	*
	* @author Guillermo Briceño
	* @since 0.1
	*/
	
	public function registroActividadAction(){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$app = Slim::getInstance();
		$rol = $auth->get_role();

		$request = $app->request;
		$current_page = $request->get('page');
		Paginator::currentPageResolver(function() use ($current_page) {
			return $current_page;
		});
		if(null !== $request->get('s') && !empty($request->get('s'))){
			$s = $request->get('s');
			$logs = Log::where('comment', 'LIKE', '%'.$s.'%')->orderBy('date', 'DESC')->get();
		}
		else{
			$logs = Log::whereNotNull('comment')->orderBy('date', 'DESC')->get();
		}
		$count =	Log::count();
		$this->render('autores-registro-actividad', array(
			'rol' => $rol,
			'nombre_usuario' => $nombre_usuario,
			'nombre_completo' => $nombre_completo,
			'logs' => $logs,
			'count' => $count,
			'avatar' => $avatar,
			'user_id' => $user_id,
			'request' => $request)
		);
	}


	/**
	* Lista todos los alumnos
	*
	* @author Guillermo Briceño
	* @since 0.1
	*/

	public function listarAlumnosAction(){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		$app = Slim::getInstance();

		$request = $app->request;
		$current_page = $request->get('page');
		Paginator::currentPageResolver(function() use ($current_page) {
			return $current_page;
		});

		if(null !== $request->get('s') && !empty($request->get('s'))){
			$s = $request->get('s');

			$alumnos = Person::join('alumno','alumno.persona_id','=','persona.id')
			->groupby('persona.id')
			->distinct()
			->where('persona.name','LIKE', $s.'%')
			->orWhere('persona.last_name','LIKE', $s.'%')
			->orWhere('persona.m_name','LIKE', $s.'%')
			->orderBy('persona.name', 'ASC')
			->get();
		}
		else{
			$alumnos = Person::join('alumno','alumno.persona_id','=','persona.id')
			->groupby('persona.id')
			->distinct()->whereNotNull('name')
			->whereNotNull('run')->where('name','<>', '')
			->orderBy('name', 'ASC')
			->get();
		}

		$this->render('autores-estudiantes', array(
			'rol' => $rol,
			'alumnos' => $alumnos,
			'nombre_usuario'=> $nombre_usuario,
			'avatar' => $avatar,
			'nombre_completo'=>$nombre_completo,
			'user_id' => $user_id,
			'request'	 => $request)
		);
	}

	public function listarProfesoresAction(){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		$app = Slim::getInstance();

		$request = $app->request;
		$current_page = $request->get('page');
		Paginator::currentPageResolver(function() use ($current_page) {
			return $current_page;
		});

		if(null !== $request->get('s') && !empty($request->get('s'))){
			$s = $request->get('s');

			$profesores = Person::select(
				'persona.id as id',
				DB::RAW("FORMAT(persona.run, 0, 'de_DE') as run"),
				DB::RAW("UPPER(persona.dv) as dv"),
				'persona.name as name',
				'persona.last_name as last_name',
				'persona.nacionalidad as nacionalidad'
				)
			->join('profesor','profesor.persona_id','=','persona.id')
			->groupby('persona.id')
			->distinct()
			->where('persona.name','LIKE', $s.'%')
			->orWhere('persona.last_name','LIKE', $s.'%')
			->orWhere('persona.m_name','LIKE', $s.'%')
			->orderBy('persona.name', 'ASC')
			->get();
		}
		else{
			$profesores = Person::select(
				'persona.id as id',
				DB::RAW("FORMAT(persona.run, 0, 'de_DE') as run"),
				DB::RAW("UPPER(persona.dv) as dv"),
				'persona.name as name',
				'persona.last_name as last_name',
				'persona.nacionalidad as nacionalidad'
				)
			->join('profesor','profesor.persona_id','=','persona.id')
			->groupby('persona.id')
			->distinct()->whereNotNull('name')
			->whereNotNull('run')->where('name','<>', '')
			->orderBy('name', 'ASC')
			->get();
		}

		$instituciones = Institution::select('id','name')->orderBy('name','asc')->get();
		$roles = Role::orderBy('name','desc')->get();

		$this->render('autores-docentes', array(
			'rol' => $rol,
			'profesores' => $profesores,
			'nombre_usuario'=> $nombre_usuario,
			'avatar' => $avatar,
			'nombre_completo'=>$nombre_completo,
			'user_id' => $user_id,
			'request' => $request,
			'instituciones' => $instituciones,
			'roles' => $roles
		));
	}

	public function agregarProfesorAction(){		
		$auth = new Auth();
		if(!$auth->is_role_by_name('admin')){
			Log::insert(['comment' => 'El usuario '.$auth->get_name().' ha intentado agregar un usuario sin ser adminstrador', 'date' => date('Y-m-d H:i:s')]);
		}

		$slim = Slim::getInstance();

		$params = $slim->request->post();
		// crear persona si no existe
		if($params['tipo-form'] == 'Nuevo'){
			$persona_id = Person::insertGetId([
				'username' => $params['persona_username'],
				'name' => mb_strtoupper($params['persona_name'], 'UTF-8'),
				'last_name' => mb_strtoupper($params['persona_last_name'], 'UTF-8'),
				'm_name' => mb_strtoupper($params['persona_m_name'], 'UTF-8'),
				'run' => $params['persona_rut'],
				'dv' => $params['persona_dv'],
				'nacionalidad' => mb_strtoupper($params['persona_nacionalidad'], 'UTF-8'),
				'gender' => $params['persona_gender'],
				'birthday' => $params['persona_birthday'],
				'phone' => $params['persona_phone'],
				'email' => $params['persona_mail'],
				'avatar' => 'user.png',
				'role' => $params['persona_role'],
				'institucion_id' => $params['persona_institucion_id']
			]);	
		}elseif($params['tipo-form'] == 'Persona'){
			$persona_id = $params['persona_id'];
		}
		Profesor::insertGetId([
			'anios_en_institucion' => $params['profesor_anios_en_institucion'],
			'cargo' => mb_strtoupper($params['profesor_cargo'], 'UTF-8'),
			'unidad_academica_trabaja' => mb_strtoupper($params['profesor_unidad_academica_trabaja'], 'UTF-8'),
			'unidad_academica_region' => mb_strtoupper($params['profesor_unidad_academica_region'], 'UTF-8'),
			'unidad_academica2_trabaja' => mb_strtoupper($params['profesor_unidad_academica2_trabaja'], 'UTF-8'),
			'unidad_academica2_region' => mb_strtoupper($params['profesor_unidad_academica2_region'], 'UTF-8'),
			'nivel_formacion_academica' => mb_strtoupper($params['profesor_nivel_formacion_academica'], 'UTF-8'),
			'titulo_obtenido' => mb_strtoupper($params['profesor_titulo_obtenido'], 'UTF-8'),
			'institucion_titulo' => mb_strtoupper($params['profesor_institucion_titulo'], 'UTF-8'),
			'pais_titulo' => mb_strtoupper($params['profesor_pais_titulo'], 'UTF-8'),
			'fecha_titulo' => mb_strtoupper($params['profesor_fecha_titulo'], 'UTF-8'),
			'persona_id' => $persona_id
		]);

		$slim->response->redirect('usuarios/usuario/'.$persona_id);
	}

	public function exportarAction($id){
		$auth = new Auth();
		if(!$auth->is_valid())
			 $this->redirect($this->app->urlFor('Login:index'));
		$user_id = $auth->get_id();
		$nombre_usuario = $auth->get_nombre();
		$nombre_completo = $auth->get_name();
		$avatar = $auth->get_avatar();
		$rol = $auth->get_role();
		//$person	= new Person();
		$persona = Person::select(
			'persona.id as id',
			'persona.username as username',
			'persona.name as name',
			'persona.last_name as last_name',
			'persona.m_name as m_name',
			'persona.run as run',
			'persona.dv as dv',
			'persona.nacionalidad as nacionalidad',
			'persona.gender as gender',
			'persona.birthday as birthday',
			'persona.phone as phone',
			'persona.email as email',
			'persona.avatar as avatar',
			'persona.role as role',
			'institucion.name as institucion')
		->join('institucion','institucion.id','=','persona.institucion_id')
		->where('persona.id', $id)
		->first();
		$alumnos = Alumno::where('persona_id', $id)->get();
		$exalumnos = ExAlumnos::select(
			'ex_alumnos.anio_egreso as anio_egreso',
			'area_desempenio.nombre as area')
		->join('area_desempenio', 'area_desempenio.id', '=', 'ex_alumnos.area_desempenio_id')
		->where('persona_id', $id)
		->get();

		$profesor_ids = Profesor::select('id')->where('persona_id', $id)->get()->toArray();
		$profesores = Profesor::whereIn('id', array_values($profesor_ids))->get();

		// Publicaciones
		$publicaciones = Publicacion::select(
			'publicacion.id as id',
			'publicacion.titulo as titulo',
			'publicacion.anio as anio',
			'publicacion.volumen as volumen',
			'revista.nombre as revista')
		->join('revista', 'revista.id', '=', 'publicacion.revista_id')
		->whereIn('publicacion.profesor_id', array_values($profesor_ids))
		->get();
		// Proyectos
		$proyectos = Proyecto::select(
			'proyecto.id as id',
			'proyecto.nombre as nombre',
			'proyecto.anio_inicio as anio_inicio',
			'proyecto.anio_fin as anio_fin',
			'linea_investigativa.nombre as linea_investigativa',
			'financiamiento.nombre as financiamiento')
		->join('linea_investigativa', 'linea_investigativa.id', '=', 'proyecto.linea_investigativa_id')
		->join('financiamiento', 'financiamiento.id', '=', 'proyecto.financiamiento_id')
		->whereIn('proyecto.profesor_id', array_values($profesor_ids))
		->get();
		// Tesis
		$tesis = Tesis::select(
			'tesis.id as id',
			'tesis.titulo as titulo',
			'tesis.tipo as tipo',
			'tesis.año as año',
			'tesis.nombre_alumno',
			'profesor_has_tesis.participacion as participacion')
		->join('profesor_has_tesis', 'profesor_has_tesis.tesis_id', '=', 'tesis.id')
		->whereIn('profesor_has_tesis.profesor_id', array_values($profesor_ids))
		->get();
		// Congresos
		$congresos = Congreso::select(
			'congreso.id as id',
			'congreso.nombre as nombre',
			'congreso.titulo as titulo',
			'congreso.año as año',
			DB::raw("DATE_FORMAT(congreso.fecha_inicio, '%d-%m-%Y') as fecha_inicio"),
			DB::raw("DATE_FORMAT(congreso.fecha_termino, '%d-%m-%Y') as fecha_termino"),
			'congreso.ciudad as ciudad',
			'congreso.pais as pais')
		->join('persona_has_congreso', 'persona_has_congreso.congreso_id', '=', 'congreso.id')
		->where('persona_has_congreso.persona_id', $id)
		->get();
		// Experiencia Académica
		$experiencia_academica = ExperienciaAcademica::whereIn('profesor_id', array_values($profesor_ids))
		->orderBy('año_inicio','desc')
		->get();
		// Administración Académica
		$administracion_academica = AdministracionAcademica::select(
			'administracion_academica.id as id',
			'administracion_academica.cargo as cargo',
			'administracion_academica.año_inicio as año_inicio',
			'administracion_academica.año_fin as año_fin',
			'institucion.name as institucion')
		->join('institucion', 'institucion.id', '=', 'administracion_academica.institucion_id')
		->whereIn('profesor_id', array_values($profesor_ids))
		->orderBy('año_inicio','desc')
		->get();
		// Experiencia Laboral
		$experiencia_laboral = ExperienciaLaboral::where('persona_id', $id)
		->orderBy('año_inicio','desc')
		->get();
		// Otros
		$datos_extra = DatoExtra::where('persona_id', $id)
		->orderBy('id','desc')
		->get();

		$perfil_lineas_investigativas = [];
		foreach ($proyectos as $proyecto) {
			array_push($perfil_lineas_investigativas, $proyecto->linea_investigativa);
		}
		$perfil_lineas_investigativas = array_unique($perfil_lineas_investigativas);
		$html = '';
		if(file_exists("../public/img/personas/".$persona->avatar)){
			$html .= 
			'<div style="width:50%;">
				<img src="../public/img/personas/'.$persona->avatar.'" style="border-radius:50%;width:160px; max-width:100%;margin:auto;">
			</div>';
		}
		else{
			$html .= 
			'<div style="width:50%;">
				<img src="../public/img/personas/user.png" style="border-radius:50%;width:160px; max-width:100%;margin:auto;">
			</div>';
		}
		$html .=
		'<div style="text-align:right;margin-left:25%">
			<p><i>'.$persona->institucion.'</i></p>
			<h2>'.$persona->name.' '.$persona->last_name.' '.$persona->m_name.'</h2>';
		foreach ($profesores as $profesor){
			$html .= 
			'<h6>'.$profesor->titulo_obtenido.' '.$profesor->institucion_titulo.' '.$profesor->pais_titulo.'</h6>';
		}
		$html .= 
		'</div>';
		if(count($perfil_lineas_investigativas) > 0){
			$html .= 
			'<div style="width:100%; margin-top:100px;">
				<p><b>Líneas Investigativas:</b></p>
				<ul>';
			foreach($perfil_lineas_investigativas as $linea_investigativa){
				$html .=
					'<li>'.$linea_investigativa.'</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}

		if(count($publicaciones) > 0){
			$html .= 		
			'<div style="width:100%; margin-top:10px;">
				<p><b>Publicaciones</b></p>
				<ul>';
			foreach($publicaciones as $pu){
				$html .=
					'<li><i>'.$pu->titulo.'</i>, publicado en '.$pu->revista.' volumen '.$pu->volumen.', '.$pu->anio.'.</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}
		
		if(count($proyectos) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Proyectos</b></p>
				<ul>';
			foreach($proyectos as $pro){
				$html .=
					'<li><i>'.$pro->nombre.'</i>, '.$pro->linea_investigativa.'. '.$pro->anio_inicio.' - '.$pro->anio_fin.'.</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}
		
		if(count($tesis) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Tesis guiadas</b>
				<ul>';
			foreach($tesis as $te){
				$html .=
					'<li><i>'.$te->titulo.'</i>, '.$te->año.'. Participación: '.$te->participacion.'.</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}
		
		if(count($congresos) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Congresos Asistidos</b>
				<ul>';
			foreach($congresos as $con){
				$html .=
					'<li><i>'.$con->titulo.'</i>, <b>'.$con->nombre.'</b>, '.$con->año.'.</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}
		
		if(count($experiencia_academica) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Experiencia Académica</b>
				<ul>';
			foreach($experiencia_academica as $ea){
				if($ea->año_fin == null){
					$html .=
					'<li>'.$ea->descripcion.'. '.$ea->departamento.' '.$ea->institucion.', '.$ea->año_inicio.' a la fecha.</li>';
				}
				else if($ea->año_inicio == $ea->año_fin){
					$html .=
					'<li>'.$ea->descripcion.'. '.$ea->departamento.' '.$ea->institucion.', '.$ea->año_inicio.'.</li>';
				}
				else{
					$html .=
					'<li>'.$ea->descripcion.'. '.$ea->departamento.' '.$ea->institucion.', '.$ea->año_inicio.' - '.$ea->año_fin.'.</li>';
				}
			}
			$html .= 
				'</ul>
			</div>';
		}

		if(count($administracion_academica) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Administración Académica</b>
				<ul>';
			foreach($administracion_academica as $aa){
				if($aa->año_fin == null){
					$html .=
					'<li>'.$aa->cargo.', '.$aa->institucion.', '.$aa->año_inicio.' a la fecha.</li>';
				}
				else if($aa->año_inicio == $aa->año_fin){
					$html .=
					'<li>'.$aa->cargo.', '.$aa->institucion.', '.$aa->año_inicio.'.</li>';
				}
				else{
					$html .=
					'<li>'.$aa->cargo.', '.$aa->institucion.', '.$aa->año_inicio.' - '.$aa->año_fin.'.</li>';
				}
			}
			$html .= 
				'</ul>
			</div>';
		}

		if(count($experiencia_laboral) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Experiencia Laboral</b>
				<ul>';
			foreach($experiencia_laboral as $el){
				if($el->año_fin == null){
					$html .=
					'<li>'.$el->descripcion.', '.$el->empresa.', '.$el->año_inicio.' a la fecha.</li>';
				}
				else if($el->año_inicio == $el->año_fin){
					$html .=
					'<li>'.$el->descripcion.', '.$el->empresa.', '.$el->año_inicio.'.</li>';
				}
				else{
					$html .=
					'<li>'.$el->descripcion.', '.$el->empresa.', '.$el->año_inicio.' - '.$el->año_fin.'</li>';
				}
			}
			$html .= 
				'</ul>
			</div>';
		}			

		if(count($datos_extra) > 0){
			$html .=
			'<div style="width:100%; margin-top:10px;">
				<p><b>Otros Datos</b>
				<ul>';
			foreach($datos_extra as $do){
				$html .=
					'<li>'.$do->detalle.'</li>';
			}
			$html .= 
				'</ul>
			</div>';
		}

		$pdf = new Dompdf();
		$pdf->loadHTML($html);
		$pdf->render();
		return $pdf->stream($persona->username.'_CV');
	}
	private function checkAvatar($avatar){
		$target_dir = "../public/img/personas/";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		if(isset($_POST["submit"])) {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
		}
		// Check if file already exists
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		// Check file size
		if ($_FILES["fileToUpload"]["size"] > 500000) {
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
		&& $imageFileType != "gif" ) {
			echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
			$uploadOk = 0;
		}
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
				echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
	}
}
