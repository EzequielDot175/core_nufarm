<?php 
	/**
	* @internal Utils
	*/
	class Utils
	{
		
		public function __construct()
		{

		}


		/**
		 * Si existe el indice en la variable post se ejecuta el callback
		 */
		public static function POST($var,$callback){
			if(isset($_POST[$var])):
				call_user_func($callback);			
			endif;
		}


		public static function ifpost($name,$default = ''){
			return (isset($_POST[$name]) ? $_POST[$name] : $default );
		}

		public static function detectOnPost($array,$returnData = false){
			foreach($array as $key => $val):
				if(isset($_POST[$val])):
					if($returnData):
						return $_POST[$val];
					else:
						return $val;
					endif;
					break;	
				endif;
			endforeach;
		}

		public static function bannedLocations(){
			$is_banned = false;
			$locate =  $_SERVER['REQUEST_URI'];
			$banned = array('formulario');

			foreach($banned as $key => $val):
				if(strpos($locate,$val)){
					$is_banned = true;
				}
			endforeach;

			return $is_banned;
		}

	}

 ?>