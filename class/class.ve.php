<?php 
	/**
	* @internal [<description>]
	*/
	class VendedorEstrella extends DB
	{
		public $role = Null;
		
		public function __construct()
		{
			parent::__construct();
		}

		// public function byDate

		public function getByVendedor($id,$date){
			print_r($id);
			print_r($date);
		}

		public static function  formatNumbers($num){
			echo number_format($num,0 ,",", ".");
		}

		public function getByCliente($obj){
			// $date = explode("_", $obj->date);

			if($this->checkClosedPeriod($obj->date)):
				/**
				 * Si el periodo existe..
				 */
				$date = explode('_', $obj->date);
				$sel = $this->prepare(self::VE_ALL_DATE_BY_CLIENT);
				$sel->bindParam(':id',$obj->cliente, PDO::PARAM_INT);
				$sel->bindParam(':inicio',$date[0], PDO::PARAM_STR);
				$sel->bindParam(':fin',$date[1], PDO::PARAM_STR);
				$sel->execute();
				$from_reg_anual = $sel->fetchAll();
				return $from_reg_anual;
			else:
				$periodos = $this->periodosReales();
				$ultimo_periodo = array_pop($periodos);
				$sel = $this->prepare(self::VE_CLIENTFACTBYID);
				$sel->bindParam(':id', $obj->cliente, PDO::PARAM_INT);
				$sel->bindParam(':periodo_anterior', $ultimo_periodo->inicio, PDO::PARAM_STR);


				$sel->execute();
				return $sel->fetchAll();
			endif;

		}

		public function getMailDataGenerator($mail){
			$period = $this->periodos();
			$last = array_pop($period);

			$user = new Usuario();
			$id = $user->getByMail($mail);
			$obj = new stdClass();
			$obj->{"cliente"} = $id->id;
			$obj->{"date"} = $last->inicio."_".$last->fin;
			$data = $this->getByCliente($obj);



			$result = new stdClass();
			$result->{"ultimo_total"} = (double)$data[0]->ultimo_total;
			$result->{"total"} = (double)$data[0]->total;
			$result->{"total_clave"} = (double)$data[0]->total_prod_clave;
			$result->{"porcentaje_clave"} = $this->getPercent($data[0]->total_prod_clave, $data[0]->total);

			return $result;
		}


		public function getMailDataGeneratorSeller($email){

			
			try {
				$period = $this->periodos();
				$last = array_pop($period);
				$seller = new Vendedor();
				$id = $seller->getIdByEmail($email);
				$period = $last->inicio."_".$last->fin;
				$data = $this->getAllByAuth($id, $period);

				$collection = array();

				foreach($data as $key => $val):
					$result = new stdClass();
					$result->{'cliente'} = $val->cliente;
					$result->{"ultimo_total"} = (double)$val->ultimo_total;
					$result->{"total"} = (double)$val->total;
					$result->{"total_clave"} = (double)$val->total_prod_clave;
					$result->{"porcentaje_clave"} = $this->getPercent($val->total_prod_clave, $val->total);

					$collection[] =$result;
				endforeach;

				return $collection;
			

			} catch (Exception $e) {
				echo $e->getMessage();
			}
			
		}

		public function getPercent($clave, $total){
			if ($total != 0 && !is_null($total)) {
				return ((double)$clave / (double)$total) * 100;
			}else{
				return 0;
			}
		}


		public function getAllByAuth($forceAuth = null, $period = null){
			$id = (!is_null($forceAuth) ? $forceAuth : Auth::idAdmin());

			// Periodos existentes que ya fueron cerrados y guardados en ve_registro_anual
			$periodo = (is_null($period) ? $_POST['params']['date'] : $period);
			
			/**
			 * Compruebo que no sea un periodo cerrado y guardado en base de datos
			 */


			if($this->checkClosedPeriod($periodo)):

				$date = explode('_', $periodo);
				$sel = $this->prepare(self::VE_ALL_DATE);
				$sel->bindParam(':id',$id, PDO::PARAM_INT);
				$sel->bindParam(':inicio',$date[0], PDO::PARAM_STR);
				$sel->bindParam(':fin',$date[1], PDO::PARAM_STR);
				$sel->execute();
				$from_reg_anual = $sel->fetchAll();
				return $from_reg_anual;
			
			else:
				/**
				 * @internal Significa que el periodo no esta 
				 * registrado en la base de datos por lo tanto 
				 * busco en los resultados creados en la base de datos
				 */

				$periodos = $this->periodosReales();
				$ultimo_periodo = array_pop($periodos);
				$sel = $this->prepare(self::VE_CLIENTFACTBYIDVENDEDOR);
				$sel->bindParam(':id', $id, PDO::PARAM_INT);
				$sel->bindParam(':periodo_anterior', $ultimo_periodo->inicio, PDO::PARAM_STR);
				$sel->execute();

				return $sel->fetchAll();

			endif;
		}
		public static function calcFactTotal($curr_total, $old_total){
			if($curr_total != 0):
				return round( ($curr_total / $old_total) * 100 );
			else:
				return 0;
			endif;
		}

		public static function calcFactTotalClave($total, $clave){
			if($total == 0 && $clave == 0 || $clave == 0):
				return 0;
			else:
				return round( ($clave / $total) * 100 );
			endif;
		}

		public static function formatCurrentPeriod($init, $final){
			$dInit = new DateTime($init);
			$dFinal = new DateTime($final);
			return $dInit->format('Y')."/".$dFinal->format('Y');
		}

		public function getPrize(){

		}

		public static function widthbar($num){
			switch ($num) {
				case '0':
					return 0;
					break;
				case '1':
					return 30.6;
					break;
				case '2':
					return 61.6;
					break;
				case '3':
					return 100;

					break;
				
				default:
					return 0;
					break;
			}
		}
		public function getPrizeCategory($total, $num){
			$sel = $this->query(self::VE_PREMIOS)->fetchAll();
			$prize = array();
			$categoria = 0;
		

			if($total >= 100):
				
			
				foreach($sel as $key => $val):
					
						$min = $val->min_req;
						$max = ($val->max_req == 0 ? 10000000 : $val->max_req );
					
						if($num >= $min && $num <= $max):
						$categoria = $val->categoria;
						endif;

				endforeach;

			endif;

			return $categoria;
		}

		public function updateFacturacion($data,$id){
			// print_r($data);
			$std = new stdClass();
			$total = 0;
			$total_prod_clave = 0;
			foreach($data as $key => $val):
				$total += (float)$val['total'];
				$total_prod_clave += (float)$val['total_prod_clave'];
				@$std->{ucwords($key)}->{'facturacion_total'} = (float)$val['total'];
				@$std->{ucwords($key)}->{'facturacion_prod_clave'} = (float)$val['total_prod_clave'];
			endforeach;

			$data_facturacion = json_encode($std);
			// echo($id);
			
			$upd = $this->prepare(self::VE_UPDATE_CURRENT_PERIOD_BYID);
			$upd->bindParam(':id', $id, PDO::PARAM_INT);
			$upd->bindParam(':data', $data_facturacion, PDO::PARAM_STR);
			$upd->bindParam(':total', $total, PDO::PARAM_INT);
			$upd->bindParam(':fact_prod_clave', $total_prod_clave, PDO::PARAM_INT);
			$upd->execute();

			// echo($upd->rowCount() > 0 ? 'true' : 'false');
			// // var_dump($id);
			// die();
			$periodos = $this->periodosReales();
			$ultimo_periodo = array_pop($periodos);
			$sel = $this->prepare(self::VE_CLIENTFACTBYIDROW);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->bindParam(':periodo_anterior', $ultimo_periodo->inicio, PDO::PARAM_STR);
			$sel->execute();
			return $sel->fetch();
		}

		public function getTotales($periodo){
			$id = Auth::idAdmin();

			if($this->checkClosedPeriod($periodo)):
				$periodo = explode("_", $periodo);
				$sel = $this->prepare(self::VE_TOTAL_BY_PERIOD);
				$sel->bindParam(':id', $id, PDO::PARAM_INT);
				$sel->bindParam(':inicio', $periodo[0], PDO::PARAM_INT);
				$sel->bindParam(':fin', $periodo[1], PDO::PARAM_INT);
				$sel->execute();
				return $sel->fetch();
			else:
				$sel = $this->prepare(self::VE_TOTAL_BY_CURRENT_PERIOD);
				$sel->bindParam(':id', $id, PDO::PARAM_INT);
				$sel->execute();
				return $sel->fetch();
			endif;
			// die();
		}

		public function getResults($params){
			$obj = (Object)$params;

			if($_POST['user']['role'] != 3):
				if(empty($obj->vendedor) && empty($obj->cliente)):
					//die("1");
					return $this->allVe($obj->date);

				elseif (!empty($obj->cliente) && empty($obj->vendedor)):
					//die("2");
					return $this->getByCliente($obj);

				elseif (!empty($obj->vendedor) && empty($obj->cliente)):
					//die("3");
					return $this->getAllByAuth($obj->vendedor);
				else:
					//die("4");
					return $this->getByCliente($obj);
				endif;

			else:
				if(empty($obj->cliente)):
					return $this->getAllByAuth();
				else:
					return $this->getByCliente($obj);
				endif;
			endif;

		}

		public function prevPeriod($id = null){
			$id = ( is_null($id) ? Auth::id() : $id);
			$periodos = $this->periodosReales();
			$last = array_pop($periodos);
			$sel = $this->prepare(self::VE_GET_PREV_PERIOD);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->bindParam(':init', $last->inicio, PDO::PARAM_STR);
			$sel->execute();
			return $sel->fetch();
		}


		public function allVe($date){


			if($this->checkClosedPeriod($date)):
				$date = explode("_", $date);
				$sel = $this->prepare(self::VE_ALL_CLIENTES_VENDEDORES_BY_PERIOD);
				$sel->bindParam(':inicio', $date[0], PDO::PARAM_STR);
				$sel->bindParam(':fin', $date[1], PDO::PARAM_STR);
				$sel->execute();
				
				return $sel->fetchAll();
			else:
				$periodos = $this->periodosReales();
				$ultimo_periodo = array_pop($periodos);
				$sel = $this->prepare(self::VE_ALL_CLIENTES_VENDEDORES);
				$sel->bindParam(':periodo_anterior', $ultimo_periodo->inicio, PDO::PARAM_STR);
				$sel->execute();

				return $sel->fetchAll();
				
			endif;
		}

		public function hasOtherPeriod($user){
			if(!$this->checkLastPeriod($user)):



				echo "<pre>";
				print_r($this->periodosReales());
				echo "</pre>";
				die();

			endif;
		}

		public function checkLastPeriod($id_client){
			$sel = $this->prepare(self::VE_COUNT_REGISTERS_BY_ID);
			$sel->bindParam(':id', $id_client, PDO::PARAM_INT);
			$result = $sel->fetch();
			return ($result->cantidad > 0 ? true : false);
		}

		public function createNewHistory(){
			$ins = $this->prepare(self::VE_CREATE_NEW_HISTORY);
			$ins->prepare(':id_vendedor');
			$ins->prepare(':vendedor');
			$ins->prepare(':id_cliente');
			$ins->prepare(':cliente');
			$ins->prepare(':inicio');
			$ins->prepare(':fin');

		}

		public function setInit(){
			if(Auth::userAdmin()->role != 3):
				$setAll = $this->query(self::VE_ALL_USERFACTURACION)->fetchAll();

				foreach($setAll as $key => $val):
					if(is_null($val->id_user)):
						$this->initFactUser($val->idUsuario,$val->vendedor);
					endif;
				endforeach;
				
			else:
				$id = Auth::idAdmin();
				$rtc_clientes = $this->prepare(self::VE_USERFACTURACION);
				$rtc_clientes->bindParam(':id',$id, PDO::PARAM_INT);
				$rtc_clientes->execute();
				$rtc_clientes = $rtc_clientes->fetchAll();
			
				foreach($rtc_clientes as $key => $val):
					if(is_null($val->id_user)):
						$this->initFactUser($val->idUsuario,$val->vendedor);
					endif;
				endforeach;
			endif;

		}


		public function initFactUser($id, $vendedor = null){
			$vendedor = ( !is_null($vendedor) ? $vendedor : Auth::idAdmin() );
			$currentPeriod = array_pop($this->periodos());

			$initData = json_encode($this->initDataFacturacion());
			$inicio =  $currentPeriod->inicio;
			$fin =  $currentPeriod->fin;

			$ins = $this->prepare(self::VE_INS_FACT_INCIAL);
			$ins->bindParam(':id',$id,PDO::PARAM_INT);
			$ins->bindParam(':vendedor',$vendedor,PDO::PARAM_INT);
			$ins->bindParam(':data',$initData,PDO::PARAM_STR);
			$ins->bindParam(':start',$inicio,PDO::PARAM_STR);
			$ins->bindParam(':end',$fin,PDO::PARAM_STR);

			$ins->execute();

		}

		public function periodos(){
			$periodos = $this->query(self::VE_ANUAL)->fetchAll();
			$last = array_pop($periodos);
			array_push($periodos, $last);
			$next = $this->nextPer($last);
			array_push($periodos, $next);

			return $periodos;
		}

		public function periodosReales(){
			$periodos = $this->query(self::VE_ANUAL)->fetchAll();
			return $periodos;	
		}

		/**
		 * Verifica si hay registros sobre el periodo creado en la base de datos,
		 * de lo contrario significa que tiene que buscar en el periodo actual, el cual no esta registrado aún
		 */
		public function checkClosedPeriod($date){
			$existe = false;

			$inicio = false;
			$fin = false;
			if(!empty($date)):
				$periodos = $this->periodosReales();
				$date = explode("_", $date);

				foreach($periodos as $key => $val):
					/**
					 * Separo la fecha en 2 partes,
					 * el año y el mes,
					 * si coinciden se considera que el periodo ya tiene registros en la base y se toma como valido
					 * de lo contrario el periodo no tiene registros y aun esta en desarrollo hasta que finalize el periodo
					 */
					$inicio_std = explode("-",$val->inicio);
					array_pop($inicio_std);

					$fin_std = explode("-", $val->fin);
					array_pop($fin_std);

					/**
					 * Preparo los datos para poder comparar
					 */
					$inicio_data = explode("-", $date[0]);
					array_pop($inicio_data);

					$fin_data = explode("-", $date[1]);
					array_pop($fin_data);

					$diff_ini = count(array_diff($inicio_std, $inicio_data));
					$diff_end = count(array_diff($fin_std, $fin_data));
						
					
					if($diff_ini == 0 && $diff_end == 0):
						$existe = true;
						break;
					endif;

				endforeach;

				return $existe;
			endif;
		}


		public function getFacturacion($id){
			$sel = $this->prepare(self::VE_GETFACTURACION);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->execute();
			echo json_encode($sel->fetch());
		}

		public function hasFacturacion(){
			$id = Auth::id();
			$result = $this->prepare(self::VE_HASFACTURACION);
			$result->bindParam(':id', $id, PDO::PARAM_INT);
			$result->execute();
			$resultado = $result->fetch();

			return (Boolean)$resultado->result;
			
		}

		public function isAdmin(){
			$role = Auth::userAdmin()->role;
			if($role == 1 || $role == 2):
				return true;
			else:
				return false;
			endif;
		}


		private static function isGold($num) {
			switch ($num) {
				case '1':
					return 'NUFARM MAXX GOLD';
					break;
				case '0':
					return 'NUFARM MAXX';
					break;
				
				default:
					return '';
					break;
			}
		}

		private function nextPer($ultimo_periodo){
			$inicio = new DateTime($ultimo_periodo->inicio);
			$fin = new DateTime($ultimo_periodo->fin);
			$inicio =  $inicio->add(new DateInterval('P1Y'))->format('Y-m-d');
			$fin = $fin->add(new DateInterval('P1Y'))->format('Y-m-d');

			$proximoPeriodo = new stdClass();
			$proximoPeriodo->inicio = $inicio;
			$proximoPeriodo->fin = $fin;

			return $proximoPeriodo;
		}

		public function categoriasPremios(){
			return $this->query(self::VE_CATPREMIOS)->fetchAll();
		}

		public function getFacturacionById($id = null){
			$id = (is_null($id) ? Auth::id() : $id);
			$sel = $this->prepare(self::VE_SEL_FACT_BY_IDUSER);
			$sel->bindParam(':id', $id, PDO::PARAM_INT);
			$sel->execute();
			return $sel->fetch();
		}

		public static function getNextPeriodMonth(){
			$current = (int)date('n') - 1;
			$meses = array ("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre");
			$periodo = array ("agosto", "septiembre", "octubre", "noviembre", "diciembre", "enero", "febrero", "marzo");

			$keyfromperiod = array_search($meses[$current], $periodo);

			$current_period = array("key" => $keyfromperiod,"value" => $periodo[$keyfromperiod]);

			if(isset($periodo[$keyfromperiod + 1])){
				return array("key" => $keyfromperiod + 1,"value" => $periodo[$keyfromperiod + 1]);
			}else{
				return array("key" => $keyfromperiod,"value" => $periodo[0]);
			}
		}

		public function getGraphData($data = null,$lastPeriod){
			$graphData = new stdClass();

			$period = self::getNextPeriodMonth();
			$object = json_decode($data);

			$total = array();
			$clave = array();
			$label = array();
			$sumtotal = 0;
			$sumclave = 0;
			$key = 0;
			foreach($object as $jsonkey => $jsonval):
				$label[$key] = "";
				$sumtotal += $jsonval->facturacion_total;
				$sumclave += $jsonval->facturacion_prod_clave;
				$total[$key] = array("percent" => self::calcFactTotal($sumtotal,$lastPeriod->total),"value" => $sumtotal);
				$clave[$key] = array("percent" => self::calcFactTotalClave($sumtotal, $sumclave),"value" => $sumclave) ;
				$key++;
			endforeach;



			$graphData->{'lastColumnCharged'} = $period["key"];
			$graphData->total = array_reverse($total);
			$graphData->label = $label;
			$graphData->clave = array_reverse($clave);

			return $graphData;
		}

		private function initDataFacturacion(){
			return array ( 
				'Agosto' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Septiembre' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Octubre' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Noviembre' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Diciembre' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Enero' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Febrero' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 ), 
				'Marzo' =>	array ( 'facturacion_total' => 0, 'facturacion_prod_clave' => 0 )
			);

		}


	}
 ?>