<?php 
	/**
	* @token
	*/
	class Token 
	{
		

 		public function __construct()
		{
		}

		private static function session(){
			// @session_start();
			

			if(!isset($_SESSION['tokens'])):
				$_SESSION['tokens'] = array();			
			endif;
		}

		private static function get(){
			self::session();
			return $_SESSION['tokens'];
		}



		public static function push($value){
			self::session();

			$collection = $_SESSION['tokens'];
			if(count(self::get()) > 4):
				array_shift($collection);
			endif;

			$collection[] = $value;

			$_SESSION['tokens'] = $collection;
		}

		public static function generate(){
			$string = "random_string";
			$md5 = md5($string.rand(1000000,9000000).rand(1000000,9000000));
			self::push($md5);
		}

		public static function printToken(){
			$tokens = self::get();
			echo(array_pop($tokens));
		}

		public static function all(){
			echo "<pre>";
			print_r(self::get());
			echo "</pre>";
		}

		public static function check($token){
			self::session();
			return in_array($token, self::get());
		}



	}

 ?>