<?php
error_reporting(E_ALL);
echo '<h1>Test du client</h1>';
define('PATH', realpath('../').'/');
require_once PATH.'libs/spykee/spykee-robot/robotClient.php';
try{
	echo 'Try to connect';
	$test = new SpykeeRobotClient('Robot1', '127.0.0.1', '2000');
	echo '<h2>Connected sucefully</h2>';
}
catch (ExceptionSpykee $e){
	echo '<h2>Error</h2>';
	echo 'ERROR:'.$e->getUserMessage().'/'.$e->getMessage();
	die;
}
echo 'Connexion OK';
echo $test->Forward();
echo '<br />Fin de la page';
?>