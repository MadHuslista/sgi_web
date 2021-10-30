<?php

namespace Sso;
use Sso\MCrypt;
use Slim\Slim;
require_once 'MCrypt.php';

/**
 * Clase que genera y verifica el xml usado por el sso de la usm. El
 * retorno de la función crear xml es usado en el iframe que se inserta
 * en la página de login de la usm
 *
 */

class Ssoxml {

	private $enc;
	protected $app;

	public function __construct() {
        $this->enc = new MCrypt();
        $this->app = Slim::getInstance();
    }

	/**
	 * Crea un xml encriptado que se usa en el iframe del formulario de
	 * login. ACá se configuran varios parámetros como la url de retorno
	 * !!! importante, si la url de retorno es incorrecta, el formulario
	 * no redirigirá bien ya que acá se cambia dicho parámetro
	 *
	 * @param $id_sistema código entregado por el administrado del sistema
	 * de sso
	 * @return $xml_enviar xml encriptado
	 *
	 */
	public function crear_xml($id_sistema){
		$varXML = '';
		$url_retorno = $this->getBaseUrl().'redirect'; //$_SERVER['SERVER_NAME'].'/login.php';
		//Se crea el XML para ser pasado a la vista
		date_default_timezone_set ( 'America/Santiago');
		$fecha_hoy = new \DateTime;
		$xml = new \DomDocument('1.0', 'UTF-8');
		$root = $xml->createElement('usm');
		$root = $xml->appendChild($root);
		$child1=$xml->createElement('validacionAcceso');
		$child1 =$root->appendChild($child1);
		$child2=$xml->createElement('id_sistema',$id_sistema);
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('fecha_recepcion',$fecha_hoy->format("YmdHis"));
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('pagina_retorno',$url_retorno);//pendiente
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('estado','1');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('css','.titulo {color:#ffffff; font-size:15px; display:none;} .figura {padding:3px;padding-top:1px; color:#ffffff; } .figura2 {padding:3px;color:#ffffff; } .boton {font-size:10px;background-color:#004B85;color:#fff;border-radius:10%;border:1px solid #006BB6; margin-top: 5px;} .link {color:#ffffff;font-size:11px;padding:5px; float:left;} .version {display:none; } ');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('rut-dv','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('nombres','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('apellidos','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('cargo','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('telefono','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('email','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('nombre_usuario','');
		$child2 =$child1->appendChild($child2);
		$child2=$xml->createElement('fecha_hora_respuesta','');
		$child2 =$child1->appendChild($child2);
		$xml->formatOutput = true;
		$strings_xml = $xml->saveXML();
		//echo $strings_xml;

		$xml_encriptado = $this->enc->encrypt($strings_xml);
		$xml_enviar = urlencode($xml_encriptado);
		return $xml_enviar;
	}

    /**
     * Desencripta el xml retornado por la autenticación del usuario y
     * recupera los datos de éste. Actualmente recupera el nombre de
     * usuario, rut, nombre, apellido paterno y email.
     *
     * @param $varXML el xml recuperado del sistema sso
     * @return un array asociativo con los campos del usuario
     *
     */
	public function verificar_xml($varXML){
		    $xml_desencriptado = $this->enc->decrypt($varXML);
		    $xml = simplexml_load_string($xml_desencriptado);
		    if ($xml === false) {
		    	$error_login= "Error en respuesta de servicio de login";
		        $ingreso = 0;
		        return $error_login;
		    }
		    else{
				$usuario = array();
				$p = xml_parser_create();
		        xml_parser_set_option($p, XML_OPTION_TARGET_ENCODING, "UTF-8");
		        xml_parser_set_option($p, XML_OPTION_CASE_FOLDING, 0);
		        xml_parser_set_option($p, XML_OPTION_SKIP_WHITE, 1);
		        xml_parse_into_struct($p, $xml_desencriptado, $elementos, $llaves);
		        xml_parser_free($p);
		        foreach ($elementos as $k=>$v){
					if(isset($v["value"]))
						$usuario[$v['tag']]=$v['value'];
				}
		        //Asignación de variables de usuario
		        /*$apellidos = explode(' ', $elementos[8]['value']);
		        $usuario['username'] = $elementos[9]['value'];
				$usuario['nombre_completo'] = $elementos[7]['value'].' '.$elementos[8]['value'];
				$usuario['nombres'] = $elementos[7]['value'];
				$usuario['apellido_paterno'] = $apellidos[0];
				$usuario['apellido_materno'] = $apellidos[1];
				$usuario['rut'] = $elementos[6]['value'];
				$usuario['email'] = $elementos[10]['value'];*/

				return $usuario;
		    }
	}


	private function getBaseUrl() {
		// output: /myproject/index.php
		$currentPath = $_SERVER['PHP_SELF'];

		// output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
		$pathInfo = pathinfo($currentPath);

		// output: localhost
		$hostName = $_SERVER['HTTP_HOST'];

		// output: http://
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';

		// return: http://localhost/myproject/
		return $protocol.$hostName.$pathInfo['dirname']."/";
	}

}


?>
