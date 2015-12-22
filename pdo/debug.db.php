<?php 

	namespace Debug;
	/**
	* 
	*/
	class DBParameters
	{
		
		public static $debug = false;
		private static $config;
		

		function __construct(){}


		public static function construct(){
			
			$config = new \stdClass();
			/**
			 * Production config
			 */

			if(self::$debug){
				$config->{'hostname'} = "localhost";
				$config->{'dbname'} = "nmaxx_dev_develop";
				$config->{'username'} = "nmaxx_dev_nmaxx";
				$config->{'password'} = "]x3#WgpD}rQ#";

			}else{
				$config->{'hostname'} = "localhost";
				$config->{'dbname'} = "nmaxx_develop";
				$config->{'username'} = "nmaxx_pnufarm";
				$config->{'password'} = "K[^Xc0lsU1T(";
			}

			self::$config = $config;
		}

		public static function Username(){
			return self::$config->username;
		}
		public static function Password(){
			return self::$config->password;
		}
		public static function Hostname(){
			return self::$config->hostname;
		}
		public static function Dbname(){
			return self::$config->dbname;
		}





	}
 ?>
