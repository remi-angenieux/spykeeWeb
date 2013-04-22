<?php
error_reporting(E_ALL);
echo '<h1>Test du client</h1>';
set_include_path(get_include_path().PATH_SEPARATOR.'/home/webServer/spykeeweb/libs');
require_once 'spykee-controller/controllerClient.php';
$test = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');

//echo $test->turnleft();
//echo $test->send_mp3();
echo $test->forward();
//echo $test->audio_play();
//echo $test->audio_stop();
//echo $test->audio_play();
//echo $test->activate();
//echo $test->charge_stop();
//echo $test->dock();
//echo $test->dock_cancel();
//echo $test->test();
echo '<br />Fin de la page';
?>