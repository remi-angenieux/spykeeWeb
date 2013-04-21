<?php
error_reporting(E_ALL);
echo '<h1>Test du client</h1>';
set_include_path(get_include_path().PATH_SEPARATOR.'/home/webServer/spykeeweb/libs');
require_once 'spykee-controller/controllerClient.php';
$test = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');

echo $test->Forward();
//echo $test->audio_play();
echo '<br />Fin de la page';
?>