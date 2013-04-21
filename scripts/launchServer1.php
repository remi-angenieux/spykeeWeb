<?php
set_include_path(get_include_path().PATH_SEPARATOR.'/home/webServer/spykeeweb/libs');
require_once 'spykee-controller/controllerServer.php';

$server1 = new SpykeeControllerServer('Robot1', '172.17.6.1', '2000', 'admin', 'admin');
//$server1 = new SpykeeControllerServer('Robot1', '127.0.0.1', '2005', 'admin', 'admin');
?>