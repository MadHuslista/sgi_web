<?php

namespace SGI\Lib;
use SGI\Models\Person;
use Slim\Slim;
class Auth{

	private $roles;
	private $campos;
	private $app;
	private $roles_strings;

	public function __construct(){
		$this->roles = array (0, 1, 2, 3, 4, 5,6);
		$this->app = Slim::getInstance();
		$this->campos = array('username', 'name', 'role', 'token', 'id', 'nombre','avatar');
		$this->roles_strings = array('admin' => 2, 'digitador' => 3, 'lector' =>1);
	}


	public function is_role($int){
		$sesion = &$_SESSION;
		if($sesion['role'] == $role)
			return true;
		return false;
	}

	public function is_role_by_name($name){
		$sesion = &$_SESSION;
		if($sesion['role'] == $this->roles_strings[$name])
			return true;
		return false;
	}

	public function create_sesion($firstUser){
		$sesion = &$_SESSION;
		$options = [
					'cost' => 11,
					//'salt' => $this->app->salt,
				];
		$sesion['username'] = $firstUser->username;
		$sesion['id'] = $firstUser->id;
		$sesion['role'] = $firstUser->role;
		$sesion['name'] = $firstUser->name. ' ' . $firstUser->last_name;
		$sesion['nombre'] = $firstUser->name;
		$sesion['avatar'] = $firstUser->avatar;
		$sesion['token'] = password_hash($firstUser->username, PASSWORD_BCRYPT, $options);
	}

	public function is_valid(){
		//Verificamos que la sesión tenga todos sus campos
		$sesion = &$_SESSION;
		foreach($this->campos as $campo){
			if(!isset($sesion[$campo]))
				return false;
		}
		//verificamos el token
		if(!password_verify($sesion["username"], $sesion["token"]))
			return false;
		return true;
	}

	public function get_token(){
		$sesion = &$_SESSION;
		return $sesion["token"];
	}

	public function get_id(){
		$sesion = &$_SESSION;
		return $sesion["id"];
	}

	public function get_name(){
		$sesion = &$_SESSION;
		return $sesion["name"];
	}

	public function get_nombre(){
		$sesion = &$_SESSION;
		return $sesion["nombre"];
	}

	public function get_avatar(){
		$sesion = &$_SESSION;
		return $sesion["avatar"];
	}

	public function get_role(){
		$sesion = &$_SESSION;
		return $sesion["role"];
	}

	public function destroySession(){
		session_unset();
        session_destroy();
        session_write_close();
        $params = session_get_cookie_params();
        $this->app->setcookie(session_name(),
                    '',
                    time() - 4200,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
        );
	}

}


?>
