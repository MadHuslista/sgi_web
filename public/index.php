<?php

/**
 * Configuración básica de la aplicación
 *
 * Acá se definen las rutas y cargan y configuran los módulos básicos de la app
 *
 * @author Jorge Courbis
 * @since Sgi 0.1
 */

session_start();

// Autoloader global
require_once dirname(__FILE__) . '/../vendor/autoload.php';
use SGI\Lib\DyAcl\DyAclPDO;
use \Slim\Extras\Middleware\CsrfGuard;
// Configuración básica de la aplicación

$config = array();
$config['app'] = array(
	'name' => 'Sistema de Gestion de la informacion direccion de programas y postgrados',
	'templates.path' => dirname(__FILE__) . '/../app/SGI/views',
	'controller.class_prefix' => '\\SGI\\Controller',
	'controller.method_suffix' => 'Action',
	'controller.template_suffix' => 'twig',
	'log.enabled' => true,
	'log.level' => Slim\Log::INFO,
	'log.writer' => new Slim\Extras\Log\DateTimeFileWriter(array(
		'path' => dirname(__FILE__) . '/../app/SGI/share/logs'
	)),
	'mode' => (!empty($_ENV['SLIM_MODE'])) ? $_ENV['SLIM_MODE']: 'production',
	'view' => new \Slim\Views\Twig()
);




//Se carga el loader del .env
$Loader = new josegonzalez\Dotenv\Loader('../.env');
$Loader->parse();
$Loader->putenv(true);

// Carga de archivo de configuración
$configFile = dirname(__FILE__) . '/../app/SGI/share/config/default.php';
if (is_readable($configFile)) {
	require_once $configFile;
}
// Se crea la instancia de la aplicación con la configuración definida
$app = New \SlimController\Slim($config['app']);

// Se inicializa el logger
$log = $app->getLog();

// Se carga la configuración de la app en modo producción
$app->configureMode('production', function () use ($app) {
	$app->config(array(
		'log.enable' => true,
		'log.level' => Slim\Log::DEBUG,
		'debug' => true
	));
});
// Se carga la configuración de la app en modo desarrollo
$app->configureMode('development', function () use ($app) {
	$app->config(array(
		'log.enable' => true,
		'log.level' => Slim\Log::DEBUG,
		'debug' => true
	));
});

//ACL
$host = getenv("DBHOST");
$dbname = getenv("DBSIS");
$dbuser = getenv("DBUSER");
$dbpass = getenv("DBPASS");
//$pipeName = getenv("DBSOCKET");
$pdo = new PDO("mysql:dbname={$dbname};", $dbuser, $dbpass);
$dyAcl = new DyAclPDO($pdo, realpath("../app/SGI/share/config/dyacl.xml"));

//Cookie de Sesion con expiración de 60 minutos
$app->add(new \Slim\Middleware\SessionCookie(array(
	'expires' => '60 minutes',
	'path' => '/',
	'domain' => null,
	'secure' => false,
	'httponly' => true,
	'name' => 'sgi',
	'secret' => '2B`.ZbYP|e(bx+>l5>|j*?Lb{`/+?n1e6Y9%r+Wbf#.0`3-L<cOwkR#m6Z%6Or7v',
	'cipher' => MCRYPT_RIJNDAEL_256,
	'cipher_mode' => MCRYPT_MODE_CBC
)));

$app->salt = $config['secret']['salt'];

$app->notFound(function () use ($app) {
	$view = $app->view();
	$view->setTemplatesDirectory('../app/SGI/views/');
	$app->render('404.twig');
});

$app->add(new CsrfGuard());



//Twig

$view = $app->view();
$view->parserOptions = array(
	'debug' => true,
	'cache' => dirname(__FILE__) . '/../app/SGI/share/cache'
);

$view->parserExtensions = array(
	new \Slim\Views\TwigExtension(),
);

$templatesPath = dirname(__FILE__) . '/../app/SGI/views';
$view->setTemplatesDirectory($templatesPath);

//Eloquent
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($config['db']);
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher(new Illuminate\Container\Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();


//Rutas
$app->addRoutes(array(

	'/' => 'Index:index',
	'/login' => 'Login:index',
	'/logout' => 'Login:logout',
	'/redirect' => 'Login:redirect',
	'/en-construccion' => 'Index:construccion',
	'/prohibido' => 'Index:prohibido',

	'/usuario/agregar' => 'Usuario:agregarUsuario',
	'/usuario/editar/:id' => 'Usuario:editarUsuario',
	'/usuario/eliminar/:id' => 'Usuario:eliminarUsuario',
	'/usuarios/usuario/:id' => 'Usuario:usuario',
	'/usuario/cv/:id' => 'Usuario:exportar',
	'/usuarios/usuario/:id/agregar-proyecto' => 'Usuario:agregarProyecto',
	'/usuarios/usuario/:id/eliminar-proyecto' => 'Usuario:eliminarProyecto',
	'/usuarios/usuario/:id/agregar-publicacion' => 'Usuario:agregarPublicacion',
	'/usuarios/usuario/:id/eliminar-publicacion' => 'Usuario:eliminarPublicacion',
	'/revistas/listar-revistas' => 'Usuario:listarRevistas',
	'/revistas/agregar-revista' => 'Usuario:agregarRevista',
	'/revistas/editar-revista' => 'Usuario:editarRevista',
	'/revistas/eliminar-revista' => 'Usuario:eliminarRevista',
	'/usuarios/usuario/:id/agregar-tesis' => 'Usuario:agregarTesis',
	'/usuarios/usuario/:id/eliminar-tesis' => 'Usuario:eliminarTesis',
	'/usuarios/usuario/:id/agregar-congreso' => 'Usuario:agregarCongreso',
	'/usuarios/usuario/:id/eliminar-congreso' => 'Usuario:eliminarCongreso',
	'/usuarios/usuario/:id/agregar-adm-academica' => 'Usuario:agregarAdmAcademica',
	'/usuarios/usuario/:id/eliminar-adm-academica' => 'Usuario:eliminarAdmAcademica',
	'/usuarios/usuario/:id/agregar-exp-academica' => 'Usuario:agregarExpAcademica',
	'/usuarios/usuario/:id/eliminar-exp-academica' => 'Usuario:eliminarExpAcademica',
	'/usuarios/usuario/:id/agregar-exp-laboral' => 'Usuario:agregarExpLaboral',
	'/usuarios/usuario/:id/eliminar-exp-laboral' => 'Usuario:eliminarExpLaboral',
	'/usuarios/usuario/:id/agregar-dato-extra' => 'Usuario:agregarDatoExtra',
	'/usuarios/usuario/:id/eliminar-dato-extra' => 'Usuario:eliminarDatoExtra',
	'/usuarios/listar-usuarios' => 'Usuario:listarUsuarios',
	'/usuarios/registro-actividad' => 'Usuario:registroActividad',

	'/alumnos/listar-alumnos' => 'Usuario:listarAlumnos',
	'/profesores/listar-profesores' => 'Usuario:listarProfesores',
	'/agregar-profesor' => 'Usuario:agregarProfesor',

	'/api/departamentos_color' => 'Api:departamentosColor',
	'/api/personas' => 'Api:personas',
	'/api/get_publicaciones' => 'Api:getPublicaciones',
	'/api/get_publicaciones_persona' => 'Api:getPublicacionesPersona',
	'/api/get_proyectos_persona' => 'Api:getProyectosPersona',
	'/api/get_congresos_persona' => 'Api:getCongresosPersona',
	'/api/get_tesis_persona' => 'Api:getTesisPersona',
	'/api/check_rut_profesor' => 'Api:checkRutProfesor',

	'/api/save_canvas' => 'Api:saveCanvas',

	'/administracion/carga/agregar-alumnos' => 'Admin:cargaAgregarAlumnos',

	############################################################################################################
	######################################### Indicadores de Academicos  ################################3######
	############################################################################################################

	// Indicadores Academicos 1
	'/indicadoresacademicos/matricula-nueva-anio-1' => 'IndicadoresAcademicos:matriculaNuevaPrimerAnio',
	'/indicadoresacademicos/vacantes-ofrecidas' => 'IndicadoresAcademicos:vacantesOfrecidas',
	'/indicadoresacademicos/caracterizacion-estudiantes' => 'IndicadoresAcademicos:caracterizacionEstudiantes',
	'/indicadoresacademicos/tasa-retencion-anio-1' => 'IndicadoresAcademicos:tasaRetencionPrimerAnio',
	'/indicadoresacademicos/tasa-retencion-total' => 'IndicadoresAcademicos:tasaRetencionTotal',

	'/indicadoresacademicos/progresion-academica' => 'IndicadoresAcademicos:progresionAcademica',
	'/indicadoresacademicos/desercion-academica' => 'IndicadoresAcademicos:desercionAcademica',
	'/indicadoresacademicos/eliminacion-academica' => 'IndicadoresAcademicos:eliminacionAcademica',
	'/indicadoresacademicos/tiempo-permanencia' => 'IndicadoresAcademicos:tiempoPermanencia',


	'/indicadoresacademicos/tasa-egreso-por-cohorte' => 'IndicadoresAcademicos:tasaEgresoPorCohorte',
	'/indicadoresacademicos/tasa-titulacion-por-cohorte' => 'IndicadoresAcademicos:tasaTitulacionPorCohorte',
	'/indicadoresacademicos/tasa-titulacion-oportuna-por-cohorte' => 'IndicadoresAcademicos:tasaTitulacionOportunaPorCohorte',

	// Indicadores Academicos 2
	'/indicadoresacademicos/tiempo-real-titulacion' => 'IndicadoresAcademicos:tiempoRealTitulacion',
	'/indicadoresacademicos/empleabilidad-laboral-cohorte' => 'IndicadoresAcademicos:empleabilidadLaboralCohorte',
	'/indicadoresacademicos/tiempo-insercion-cohorte' => 'IndicadoresAcademicos:tiempoInsercionCohorte',
	'/indicadoresacademicos/tabla-asignaturas' => 'IndicadoresAcademicos:tablaAsignaturas',
	'/indicadoresacademicos/asignaturas-criticas' => 'IndicadoresAcademicos:asignaturasCriticas',
	'/indicadoresacademicos/integrantes-evaluacion-plan-estudio' => 'IndicadoresAcademicos:integrantesEvaluacionPlanEstudio',
	'/indicadoresacademicos/integrantes-evaluacion-desarrollo-asignaturas' => 'IndicadoresAcademicos:integrantesEvaluacionDesarrolloAsignaturas',
	'/indicadoresacademicos/seleccion-y-admision-regular' => 'IndicadoresAcademicos:seleccionAdmisionRegular',
	'/indicadoresacademicos/seleccion-y-admision-especial' => 'IndicadoresAcademicos:seleccionAdmisionEspecial',
	'/indicadoresacademicos/actividades-mejoramiento-academicos' => 'IndicadoresAcademicos:actividadesMejoramientoAcademico',

	############################################################################################################
	######################################### Indicadores de Vinculacion  ######################################
	############################################################################################################
	'/indicadoresvinculacion/calendario-actividades-promocion' => 'IndicadoresVinculacion:calendarioActividadesPromocion',
	'/indicadoresvinculacion/bd-ex-alumnos' => 'IndicadoresVinculacion:bdExAlumnos',
	'/indicadoresvinculacion/area-desempenio-ex-alumnos' => 'IndicadoresVinculacion:areaDesempenioExAlumnos',
	'/indicadoresvinculacion/centro-ex-alumnos' => 'IndicadoresVinculacion:centroExAlumnos',
	'/indicadoresvinculacion/actividades-ex-alumnos' => 'IndicadoresVinculacion:actividadesExAlumnos',
	'/indicadoresvinculacion/bd-empleadores' => 'IndicadoresVinculacion:bdEmpleadores',
	'/indicadoresvinculacion/convenios-vigentes' => 'IndicadoresVinculacion:conveniosVigentes',
	'/indicadoresvinculacion/ofertas-practica' => 'IndicadoresVinculacion:ofertasPractica',
	'/indicadoresvinculacion/memorias-realizadas' => 'IndicadoresVinculacion:memoriasRealizadas',


	//Exportar Excel de Vinculacion
	'/excelCalendarioActividadesPromocion' => 'IndicadoresVinculacion:excelCalendarioActividadesPromocion',
	'/excelBdExAlumnos' => 'IndicadoresVinculacion:excelBdExAlumnos',
	'/excelAreaDesempenioExAlumnos' => 'IndicadoresVinculacion:excelAreaDesempenioExAlumnos',
	'/excelCentroExAlumnos' => 'IndicadoresVinculacion:excelCentroExAlumnos',
	'/excelActividadesExAlumnos' => 'IndicadoresVinculacion:excelActividadesExAlumnos',
	'/excelBdEmpleadores' => 'IndicadoresVinculacion:excelBdEmpleadores',
	'/excelOfertasPractica' => 'IndicadoresVinculacion:excelOfertasPractica',
	'/excelMemoriasRealizadas' => 'IndicadoresVinculacion:excelMemoriasRealizadas',

	############################################################################################################
	######################################### Indicadores de Investigacion  ####################################
	############################################################################################################
	'/indicadoresinvestigacion/publicaciones' => 'IndicadoresInvestigacion:listarPublicaciones',
	'/indicadoresinvestigacion/ver-publicacion/:id' => 'IndicadoresInvestigacion:verPublicacion',
	'/indicadoresinvestigacion/proyectos' => 'IndicadoresInvestigacion:listarProyectos',

	//Exportar Excel de Investifacion
	'/excelListarPublicaciones' => 'IndicadoresInvestigacion:excelListarPublicaciones',
	'/excelListarProyectos' => 'IndicadoresInvestigacion:excelListarProyectos',

	############################################################################################################
	######################################### Indicadores de Gestion  ##########################################
	############################################################################################################
	'/indicadoresgestion/estado-acreditacion' => 'IndicadoresGestion:listarEstadoAcreditacion',
	'/indicadoresgestion/m2-academicos' => 'IndicadoresGestion:listarM2Academicos',
	'/indicadoresgestion/m2-administrativos' => 'IndicadoresGestion:listarM2Administrativos',
	'/indicadoresgestion/inversion-infraestructura' => 'IndicadoresGestion:listarInversionInfraestructura',
	'/indicadoresgestion/inversion-equipamiento' => 'IndicadoresGestion:listarInversionEquipamiento',
	'/indicadoresgestion/planes-desarrollo' => 'IndicadoresGestion:listarPlanesDesarrollo',
	'/indicadoresgestion/presupuesto-operaciones' => 'IndicadoresGestion:listarPresupuestoOperaciones',
	'/indicadoresgestion/ejecucion-presupuestaria' => 'IndicadoresGestion:listarEjecucionPresupuestaria',
	//Exportar Excel de Gestion
	'/excelEstadoAcreditacion' => 'IndicadoresGestion:excelEstadoAcreditacion',
	'/excelListarM2Academicos' => 'IndicadoresGestion:excelListarM2Academicos',
	'/excelListarM2Administrativos' => 'IndicadoresGestion:excelListarM2Administrativos',
	'/excelListarInversionInfraestructura' => 'IndicadoresGestion:excelListarInversionInfraestructura',
	'/excelListarInversionEquipamiento' => 'IndicadoresGestion:excelListarInversionEquipamiento',
	'/excelListarPlanesDesarrollo' => 'IndicadoresGestion:excelListarPlanesDesarrollo',
	'/excelListarPresupuestoOperaciones' => 'IndicadoresGestion:excelListarPresupuestoOperaciones',

	//Buscar datos mediante ajax

	'/ajax/buscarAcreditacion' => 'IndicadoresGestion:buscarAcreditacion',
	'/ajax/buscarM2InstalacionesAcademicasLab' => 'IndicadoresGestion:buscarM2InstalacionesAcademicasLab',
	'/ajax/buscarM2InstalacionesAcademicasDoc' => 'IndicadoresGestion:buscarM2InstalacionesAcademicasDoc',
	'/ajax/buscarM2Administrativos' => 'IndicadoresGestion:buscarM2Administrativos',
	'/ajax/buscarInversionInfraestructuraCC' => 'IndicadoresGestion:buscarInversionInfraestructuraCC',
	'/ajax/buscarInversionInfraestructuraCSJ' => 'IndicadoresGestion:buscarInversionInfraestructuraCSJ',
	'/ajax/buscarInversionEquipamientoCC' => 'IndicadoresGestion:buscarInversionEquipamientoCC',
	'/ajax/buscarInversionEquipamientoCSJ' => 'IndicadoresGestion:buscarInversionEquipamientoCSJ',
	'/ajax/buscarPlanesDesarrollo' => 'IndicadoresGestion:buscarPlanesDesarrollo',
	'/ajax/buscarPresupuestoOperacionalCC' => 'IndicadoresGestion:buscarPresupuestoOperacionalCC',
	'/ajax/buscarPresupuestoOperacionalCSJ' => 'IndicadoresGestion:buscarPresupuestoOperacionalCSJ',


	############################################################################################################
	######################################### Menu Lateral  ##########################################
	############################################################################################################

	//Malla curricular
	'/malla-curricular-5406' => 'MenuLateral:mallaCurricularAsignaturas',

	//PDE DIMM
	'/pde-dimm' => 'MenuLateral:pdeDimm',

	//Plan Estrategico
	'/informacion-general/plan-estrategico' => 'MenuLateral:planEstrategico',
	'/informacion-general/estatutos-institucionales' => 'MenuLateral:estatutosInstitucionales',
	'/informacion-general/proyecto-educativo' => 'MenuLateral:proyectoEducativo',
	'/informacion-general/proceso-admision' => 'MenuLateral:procesoAdmision',
	'/informacion-general/fortalecimiento-tecnicas-estudio' => 'MenuLateral:tecnicasEstudio',
	'/informacion-general/pruebas-alcance-nacional' => 'MenuLateral:pruebasAlcanceNacional',
	'/informacion-general/practicas' => 'MenuLateral:practicas',

	//Reglamentos
	'/reglamentos/estudiantes' => 'MenuLateral:reglamentosEstudiantes',
	'/reglamentos/personal' => 'MenuLateral:reglamentosPersonal',
	'/reglamentos/precesos-docentes' => 'MenuLateral:reglamentosProcesos',
	'/reglamentos/institucion-carrera' => 'MenuLateral:reglamentosInstitucionCarrera',
	'/reglamentos/titulacion' => 'MenuLateral:reglamentosTitulacion',

	//Documentos
	'/documentos/lineamientos-institucionales' => 'MenuLateral:lineamientosInstitucionales',
	'/documentos/plan-vinculacion-medio' => 'MenuLateral:vinculacionMedio',
	'/documentos/politicas-amenazas' => 'MenuLateral:politicasAmenazas',
	'/documentos/politicas-recursos' => 'MenuLateral:politicasRecursos',
	'/documentos/criterio8' => 'MenuLateral:criterio8',
	'/documentos/accesibilidad-universal' => 'MenuLateral:accesibilidadUniversal',
	'/documentos/estatutos-federacion' => 'MenuLateral:estatutosFederacion',

	'/documentos/actas-consejo-departamento' => 'MenuLateral:actasConsejo',

	//Perfil Autor
	'/perfil/autor/:id' => 'ProfesorController:profesorPerfil',

	############################################################################################################
	######################################### Gestion de Presupuesto ##########################################
	############################################################################################################

	'/presupuesto/carga-masiva' => 'PresupuestoController:cargarPresupuesto',
	'/presupuesto/cargar-presupuesto-anual' => 'PresupuestoController:cargarPresupuestoAnual',
	'/presupuesto/ya-cargado' => 'PresupuestoController:YaCargado',

	'/movimiento/carga-masiva' => 'PresupuestoController:nuevoMovimiento',
	'/movimiento/cargar-movimiento' => 'PresupuestoController:cargarMovimiento',


	'/indicadoresgestion/graficos-presupuesto' => 'PresupuestoController:VerGraficos',

	'/ajax/getCuentas' => 'PresupuestoController:getCuentas',

	'/ajax/getDatosGrafico' => 'PresupuestoController:getDatosGrafico',


	#prueba

	'/testing' => 'Index:testing'




));

$app->run();
