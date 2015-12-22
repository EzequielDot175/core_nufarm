<?php 
	ob_start();

	/**
	* @internal Clase Auth para autenticar la session del usuario
	*/
	class Auth extends DB
	{

		use Facade;



		private $id;


		public function __construct()
		{
			parent::__construct();
			self::start();
		}

		public static function startSession(){
			@session_start();				
		}

		public static function start(){
			if(!isset($_SESSION["MM_Username"])):
				@session_start();
			endif;
		}
		public static function startAdmin(){
			if(!isset($_SESSION["logged_id"])):
				@session_start();
			endif;
		}

		public static function check(){
			self::start();

			if(empty($_SESSION["MM_IdUsuario"])):
				@header('location: /');
				exit();
			endif;
		}

		public static function checkAdmin(){
			self::startAdmin();
			@ob_start();
			if(empty($_SESSION["logged_id"])):
				@header('location: ./');
				exit();
			endif;
		}


		public function checkInit(){
			$id = Auth::id();
			$sel = $this->prepare(self::USUARIO_CHECK_INIT);
			$sel->bindParam(':id', $id , PDO::PARAM_INT);
			$sel->execute();

			$result = $sel->fetch();
		
			var_dump($result);
			// var_dump((Boolean)$result->init);
			// if(!(Boolean)$result->init):
			// 	Redirect::to('formulario-inicio.php');
			// endif;
			
		}


		public function ForceAuth($id){
			@session_destroy();
			@session_start();
			$sel = $this->prepare(self::AUTH_FORCE_AUTH_USER);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->execute();
			$params = $sel->fetch();

			$this->userLogin($params->strEmail, $params->strPassword);
		}


		public function ForceAuthAdmin($id){
			@session_destroy();
			@session_start();
			$sel = $this->prepare(self::AUTH_FORCE_AUTH_USER_ADMIN);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->execute();
			$params = $sel->fetch();

			$this->userLoginAdmin($params->login, $params->password);
		}

		public static function idAdmin(){
			self::startSession();

			return ( isset($_SESSION['logged_id']) ? (int)$_SESSION['logged_id'] : false );
		}

		public static function id(){
			self::start();
			return ( isset($_SESSION['MM_IdUsuario']) ? (int)$_SESSION['MM_IdUsuario'] : false);
		}


		public function getUser(){
			$id = self::id();
			$user = $this->prepare(self::AUTH_USER);
			$user->bindParam(':id', $id, PDO::PARAM_INT);
			$user->execute();
			return $user->fetch();
		}
		
		public function getUserAdmin(){
			$id = self::idAdmin();
			$user = $this->prepare(self::AUTH_USERADMIN);
			$user->bindParam(':id', $id, PDO::PARAM_INT);
			$user->execute();
			return $user->fetch();
		} 

		public function puntosConsumidos(){
			$id = self::id();
			$sel = $this->prepare(self::AUTH_USEDPOINTS);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->execute();
			return $sel->fetch()->total;
		}

		public function login(stdClass $params){
			self::destroy();
			$user = $params->user;

			$type = $this->defineUser($user);
			$response = new stdClass();

			if ($type->isAdmin):
				return $this->authAdmin($params);
			elseif ($type->isUser):
				return $this->authUser($params);
			else:
				return false;
			endif;

		}

		public function authAdmin(stdClass $params){
			self::startSession();

			$sel = $this->prepare(self::AUTH_LOGIN_USER_ADMIN);
			$sel->bindParam(':user', $params->user, PDO::PARAM_STR);
			$sel->bindParam(':pass', $params->pass, PDO::PARAM_STR);
			$sel->execute();

			$collection = $sel->fetch();
			if($collection != false):
				$_SESSION["logged_id"] = $collection->id;
				$_SESSION["logged_role"] = $collection->role;
			endif;


			return $collection;			
			
		}

		public function authUser(stdClass $params){
			self::startSession();

			

			$sel = $this->prepare(self::AUTH_LOGIN_USER);
			$sel->bindParam(':user', $params->user, PDO::PARAM_STR);
			$sel->bindParam(':pass', $params->pass, PDO::PARAM_STR);
			$sel->execute();

			$collection = $sel->fetch();

			if($collection != false):
				$_SESSION['MM_IdUsuario'] = $collection->idUsuario;
				$_SESSION['MM_Username'] = $collection->strEmpresa;
			endif;
		

			return $collection;			
			
		}

		public static function formHasEmptyParams($collection = null){

			if(is_null($collection)){
				$collection = Auth::User();
			}

			$hasEmptyParams =  false;

			$exceptions = array(
				'idUsuario',
				'idempresa',
				'tipocliente2015',
				'vendedor',
				'tipocliente',
				'idUsuario',
				'like_promotion',
				'like_promotion_activity',
				'sellers',
				'llamado1',
				'llamado2',
				'llamado3',
				'llamado4',
				'llamado5',
				'resultado',
				'observaciones',
				'vigencia_credito',
				'entrykey',
				'gold',
				'puntos_asignados',
				'dblCredito',
				'dblAsignado',
				'dblConsumido',
				'estadocivil',
				'other_activity',
				'other_activity',
				'strCargo',
				'vendedores',
				'form'
				);


			foreach ($collection as $key => $value) {
				if(in_array($key, $exceptions)){
					unset($collection->{$key});
				}
			}

			foreach ($collection as $key => $value) {
				if($value == "" || is_null($value)){
					
					$hasEmptyParams = true;
				}
			}

			return $hasEmptyParams;
		}

		public function defineUser($user){
			$sel = $this->prepare(self::AUTH_DEFINE_USER);
			$sel->bindParam(':mail', $user, PDO::PARAM_STR);
			$sel->execute();
			$result = $sel->fetch();

			if($result):
				$obj = new stdClass();
				$obj->{'isUser'}    = (Boolean)$result->isUser;
				$obj->{'isAdmin'} = (Boolean)$result->isAdmin;
			else:
				$obj = new stdClass();
				$obj->{'isUser'}    = false;
				$obj->{'isAdmin'} = false;
				
			endif;

			return $obj;
			
		}


		public static function checkLoginAdmin(){
			self::startSession();
			$obj = new stdClass();
			$obj->{'check'} = false;
			$obj->{'type'}   = "";
			$obj->{'id'}     = null;

			if(isset($_SESSION['logged_id'])):

				$obj->{'check'} = (!empty($_SESSION['logged_id']) ? true : false);
				$obj->{'type'}   = "personal";
				$obj->{'id'}     = $_SESSION['logged_id'];

				return $obj;
			else:
				return $obj;
			endif;
		}

		public static function checkLogin(){
			self::startSession();
			$obj = new stdClass();
			$obj->{'check'} = false;
			$obj->{'type'}   = "";
			$obj->{'id'}     = null;

			if(isset($_SESSION['MM_IdUsuario'])):

				$obj->{'check'} = (!empty($_SESSION['MM_IdUsuario']) ? true : false);
				$obj->{'type'}   = "usuario";
				$obj->{'id'}     = $_SESSION['MM_IdUsuario'];
			
				return $obj;
			else:
				return $obj;
			endif;
		}

		public static function oneTypeUser(){
			self::startSession();
			if(isset($_SESSION['MM_IdUsuario']) && isset($_SESSION['logged_id'])):
				if(empty($_SESSION['MM_IdUsuario'])):
					unset($_SESSION['MM_IdUsuario']);
				elseif(empty($_SESSION['logged_id'])):
					unset($_SESSION['logged_id']);
				endif;

				if(empty($_SESSION['MM_IdUsuario']) && $_SESSION['logged_id']):
					session_destroy();
				endif;
			endif;
		}

		

		public static function consumido(){
			return self::method('puntosConsumidos');
		}

		public static function User(){
			$user = new Auth();
			return $user->getUser();
		}

		public static function UserAdmin(){
			return self::method('getUserAdmin'); 
		}

		public static function BirthDay($dat){
			$date = new DateTime($dat);
			return $date->format('d/m/Y');
		}

		public static function userLogin($user, $pass){


			$obj = new stdClass();
			$obj->user = $user;
			$obj->pass = $pass;


			return self::method('authUser', $obj);
		}

		public static function userLoginAdmin($user, $pass){
			$obj = new stdClass();
			$obj->user = $user;
			$obj->pass = $pass;
			return self::method('authAdmin', $obj);
		}

		public static function destroy(){
			@session_destroy();
		}


		public static function sForceAuth($id){
			return self::method('ForceAuth',$id);
		}

		public static function sForceAuthAdmin($id){
			return self::method('ForceAuthAdmin', $id);
		}

	}

 ?>