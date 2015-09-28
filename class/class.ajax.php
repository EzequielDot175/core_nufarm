<?php 

	/**
	* 
	*/
	class Ajax
	{
		
		public static function get($param){
			self::{$param}();
			die();
		}

		public static function call($use){
			self::{$use}();
			die();	
		}


		private static function vendedores(){
			$user = self::post('user');

			switch ($user['role']) {
				case '1':
				case '2':
					$vendedores = new Vendedor();
					$all = $vendedores->basics();
					echo json_encode($all);
					break;

				default:
					
					break;
			}
			
		}

		private static function clientes(){
			$user = self::post('user');
			$clientes = new Cliente();

			switch ($user['role']):
				case '1':
				case '2':
						if(is_null(self::post('id'))):
							$all = $clientes->basicsVe();
							echo json_encode($all);
						else:
							$all = $clientes->basicsById(self::post('id'));
							echo json_encode($all);
						endif;
					break;
				default:
				// print_r($_POST);
					$all = $clientes->basicsById($user['id']);
					echo json_encode($all);
					break;
			endswitch;
		}

		public static function checkPeriod(){
			$date = self::post('date');
			$ve = new VendedorEstrella();
			$result = $ve->checkClosedPeriod($date);
			echo ($result ? 0 : 1);
		}

		public static function updateDataFacturacion(){
			$data = self::post('data');
			$ve = new VendedorEstrella();
			$data = $ve->updateFacturacion(self::post('data'), self::post('id') );
			echo json_encode($data);
		}

		private static function filter(){
			$filter = self::post('params');
			$user = self::post('user');
			$ve = new VendedorEstrella();
			$ve->role = $user['role'];
			$collection = $ve->getResults($filter);
			echo json_encode($collection);
			// $ve->getResults($filter);
			// print_r($collection);
		}

		private static function editDataAuth(){
			$params = (Object)self::post('data');

			$format = array();
			$format['strCargo'] = $params->appointment;
			$format['telefono'] = $params->cellphone;
			$format['cumpleanos'] = $params->birthday;
			$format['compania'] = $params->company;
			$format['strNombre'] = $params->name;
			$format['strApellido'] = $params->lastName;
			$format['direccion'] = $params->companyAdress;
			$format['estadocivil'] = $params->civilStatus;
			$format['sms'] = ($params->sms ? '1' : '0');

			$usuario = new usuario();

			$result = ($usuario->edit($format) ? 'true' : 'false');
			echo($result);
		}
		
		private static function editPassword(){
			$user = new usuario();
			$password = self::post('password');
			$result = $user->editAuthPassword($password);

			echo ($result ? 'true' : 'false');
		}

		private static function AuthUser(){
			echo json_encode(Auth::User());
		}

		public static function totalByPeriod(){
			$ve = new VendedorEstrella();
			echo json_encode( $ve->getTotales(self::post('date')) );

		}

		private static function editAuth(){
			$user = self::post('data');
			$format = array();
			$format['strEmpresa']  = $user['company'];
			$format['domicilio_entrega']   = $user['direction'];
			$format['ciudad']      = $user['city'];
			$format['cp']          = $user['cod'];
			$format['telefono']    = $user['phone'];
			$format['provincia']   = $user['province'];

			$usuario = new usuario();
			$result = $usuario->edit($format);
			echo ($result ? 1 : 0);
		}

		private static function uploadLogo(){
			print_r($_POST);
			// print_r($_FILES);
			echo("end");
		}

		private static function myData(){
			$ve = new VendedorEstrella();
			$collection = $ve->getFacturacion(self::post('id'));
			print_r($collection);
		}

		private static function Periodos(){
			$ve = new VendedorEstrella();
			$collection = $ve->periodos();
			echo json_encode($collection);
		}

		private static function User(){
			$user = Auth::userAdmin();
			print_r($user);
		}

		private static function editStep3(){
			$data = self::post('data');
			$usuario = new Usuario();
			$usuario->editStep3($data);
		}

		private static function editStep4(){

			$company = self::post('company');
			$sellers = json_encode(self::post('sellers'));
			$format = array();
			$format['strEmpresa'] = $company['company'];
			$format['dir_empresa'] = $company['direction'];
			$format['ciudad_empresa'] = $company['city'];
			$format['cp_entrega'] = $company['cp'];
			$format['tel_empresa'] = $company['phone'];
			$format['prov_empresa'] = $company['province'];
			$format['vendedores'] = $sellers;

			$usuario = new Usuario();
			$response = $usuario->edit($format);
			echo ($response ? 'true' : 'false');
		}

		private static function getByCliente(){
			$obj = self::post('data');
			$obj = (Object) $obj;
			$ve = new VendedorEstrella();
			echo json_encode($ve->getByCliente($obj));
		}

		private static function catPremios(){
			$ve = new VendedorEstrella();
			$collection = $ve->categoriasPremios();
			echo json_encode($collection);
		}

		private static function selectHistorial(){
			$filtros = new Filter();
			$filtros->historial(self::post('option'));
		}

		private static function excel(){
			$collection = self::post('collection');

			$name = 'filtros_reportes'.rand(1000000,9000000);
			$excel = new Excel();
			$excel->setName($name);
			$excel->setProp(array(
				'title' => "filtros"
				));
			$excel->setFiltrosFormat($collection);
			$excel->create();
			$dir = $excel->getDir();
			if(file_exists($dir."/".$name.".xlsx")):
				echo($name.".xlsx");
			else:
				echo("false");			
			endif;

		}

		private static function loginNufarm(){
			
			$params = new stdClass();
			$params->{'user'} = self::post('user');
			$params->{'pass'} = self::post('pass');
			$auth = new Auth();
			$response = $auth->login($params);
			if($response):
				echo(json_encode($response));
			else:
				echo(json_encode(array('error' => true )));
			endif;

		}

		private static function checkAllLogged(){
			$logAdmin = Auth::idAdmin();
			$logUser = Auth::id();

			if($logAdmin):
				echo json_encode(Auth::userAdmin());
			elseif($logUser):
				echo json_encode(Auth::User());
			else:
				echo json_encode(array('error' => true));
			endif;
		}



		private static function post($name){
			return ( isset($_POST[$name]) && !empty($_POST[$name]) ? $_POST[$name] : null ) ;
		}



		/**
		 * Seteo las condiciones para que angular js pueda hacer post a php normal
		 */
		public static function Angular(){
			if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
			    $_POST = array_merge($_POST, (array) json_decode(trim(file_get_contents('php://input')), true));
			}
		}

	}







 ?>