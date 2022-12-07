<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
//Establecemos zona horaria por defecto
date_default_timezone_set('Europe/Madrid');

define('APP_NAME', 'fichador');
$arr = explode('/', $_SERVER['DOCUMENT_ROOT'] ) ;
define('FOLDER_ROOT' , str_replace(array_pop($arr),'',$_SERVER['DOCUMENT_ROOT']) );
define('FOLDER_APP' ,    FOLDER_ROOT     . "app/" . APP_NAME . "/");
define('FOLDER_CORE' ,   FOLDER_APP      . "core/" );
define('FOLDER_VENDOR',  FOLDER_ROOT     . 'vendor/');
define('FOLDER_VIEWS',   FOLDER_APP      . 'views/');
define('FOLDER_MODELS',  FOLDER_APP      . 'models/');
define('FOLDER_CSS',     FOLDER_APP      . 'src/css/');
define('FOLDER_JS',      FOLDER_APP      . 'src/js/');
define('FOLDER_IMG',     FOLDER_APP      . 'src/img/');

//Se inicia session que esta en clase segurity
$url_base = str_replace('htdocs', '', $_SERVER['DOCUMENT_ROOT']);
//configuracion general 
require(FOLDER_VENDOR . 'autoload.php');

$router = new \app\core\Controller(FOLDER_VIEWS, FOLDER_MODELS, 'login' , 'phtml');
$router->route($_REQUEST);