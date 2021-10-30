<?php

namespace SGI\Controller;
use Sso\Ssoxml;
use SGI\Models\Person;
use Slim\Slim;
use SGI\Lib\Auth;
use SGI\Models\Log;

class Login extends \SlimController\SlimController
{

	private static $varxml = "";

    public function indexAction(){
		$xml = new Ssoxml();
		$varxml = $xml->crear_xml('DIGP01');
        $this->render('login', array('varxml' => $varxml));
    }

    public function redirectAction(){
		$app = Slim::getInstance();
		$xml = new Ssoxml();
		$url = $app->urlFor('Index:index');
		$usuario_sso = $xml->verificar_xml($_GET['varXML']);
		if(is_array($usuario_sso) && count($usuario_sso) > 0){
			$persona = new Person();
			$datos = $persona->where('username', $usuario_sso["nombre_usuario"])->get();
			if (!$datos->isEmpty()){
				$firstUser = $datos->first();
				$auth = new Auth();
				$auth->create_sesion($firstUser);
				Log::insert(['comment' => 'El usuario '.$firstUser->name. ' ' . $firstUser->last_name.' ha iniciado sesión', 'date' => date('Y-m-d H:i:s')]);
				$this->render('redirect', array('url' => $url));
			}
		}
	}

	public function logoutAction(){
		$auth = new Auth();
		$auth->destroySession();
		$this->redirect($this->app->urlFor('Login:index'));
	}

}
