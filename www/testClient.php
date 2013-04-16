<?php
require_once '../libs/spykee-client/main.php';

$test = new SpykeeClient('Robot1', '127.0.0.1', '2000');

$test->turnLeft();
?>