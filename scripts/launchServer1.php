<?php
if (!defined('PATH'))
	define('PATH', realpath('../').'/');
require_once(PATH.'libs/spykee/spykee-controller/controllerServer.php');

$server1 = new SpykeeControllerServer('Robot1', '172.17.6.1', '2000', 'admin', 'admin');
?>