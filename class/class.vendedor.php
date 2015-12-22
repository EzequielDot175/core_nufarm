<?php 
	/**
	* @internal
	*/
	class Vendedor extends DB
	{
		use Facade;

		public function __construct()
		{
			parent::__construct();
		}

		public function basics(){
			$sel = $this->prepare(self::VENDEDOR_OPTIONS);
			$sel->execute();
			return $sel->fetchAll();
		}

		public function basicsPN(){
			$sel = $this->prepare(self::VENDEDOR_OPTIONS);
			$sel->execute();
			return $sel->fetchAll();
		}


		public function getIdByEmail($email){


			$sel = $this->prepare(self::VENDEDOR_GET_ID_BY_EMAIL);
			$sel->bindParam(':email', $email, PDO::PARAM_STR);
			$sel->execute();

			$result = $sel->fetch();
			if(isset($result->id)){
				return $result->id;
			}else{
				throw new Exception("Id cant be returned", 1);
			}
			// return ;
		}


		public static function options($selected = null){
			$collection = self::method('basics');
			$html = "";
			foreach($collection as $key => $val):
				if(!empty($val->nombre) && !empty($val->apellido)):
					if(!is_null($selected)):
						if($selected == $val->id):
							$html .= '<option selected="" value="'.$val->id.'">'.strtoupper($val->nombre).' '.strtoupper($val->apellido).'</option>';
						else:
							$html .= '<option value="'.$val->id.'">'.strtoupper($val->nombre).' '.strtoupper($val->apellido).'</option>';
						endif;
					else:
						$html .= '<option value="'.$val->id.'">'.strtoupper($val->nombre).' '.strtoupper($val->apellido).'</option>';
					endif;
				endif;
			endforeach;
			echo($html);
		}


		
	}

 ?>