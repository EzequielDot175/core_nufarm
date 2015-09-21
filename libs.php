<?php 
	// error_reporting(E_ALL);
	// ini_set('display_errors', 'On');
	define('APP_DIR', dirname(__FILE__));
	require_once(APP_DIR.'/class/class.template.php');
	require_once(APP_DIR.'/class/class.upload.php');
	require_once(APP_DIR.'/class/class.token.php');
	require_once(APP_DIR.'/mail/PHPMailerAutoload.php');
	require_once(APP_DIR.'/excel/PHPExcel.php');
	require_once(APP_DIR.'/interface/DBInterface.php');
	require_once(APP_DIR.'/pdo/DB.constant.php');
	require_once(APP_DIR.'/traits/facade.php');
	require_once(APP_DIR.'/traits/session.php');
	require_once(APP_DIR.'/class/class.nav.php');
	require_once(APP_DIR.'/class/class.redirect.php'); 
	require_once(APP_DIR.'/class/class.auth.php'); 
	require_once(APP_DIR.'/class/class.estados.php');
	require_once(APP_DIR.'/class/class.consultas.php');
	require_once(APP_DIR.'/class/class.provincias.php'); 



	require_once(APP_DIR.'/class/class.utils.php'); 
	require_once(APP_DIR.'/class/class.tempstock.php'); 
	require_once(APP_DIR.'/class/class.tempmaxcompra.php'); 
	require_once(APP_DIR.'/class/class.usuario.php'); 
	require_once(APP_DIR.'/class/class.stock.php'); 
	require_once(APP_DIR.'/class/class.compras.php'); 
	require_once(APP_DIR.'/class/class.producto.php'); 
	require_once(APP_DIR.'/class/class.colores.php'); 
	require_once(APP_DIR.'/class/class.shoppingcart.php'); 
	require_once(APP_DIR.'/class/class.historial.php');
	require_once(APP_DIR.'/class/class.clientes.php');
	require_once(APP_DIR.'/class/class.vendedor.php');



	require_once(APP_DIR.'/class/class.filtros.php'); 
	require_once(APP_DIR.'/class/class.ajax.php'); 
	require_once(APP_DIR.'/class/class.ve.php');
	require_once(APP_DIR.'/class/class.mail.php');
	require_once(APP_DIR.'/class/class.excel.php');


 ?>
