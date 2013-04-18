<?php
require_once 'spykee-controller/controllerClient.php';

$test = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');

$test->turnLeft();
?>