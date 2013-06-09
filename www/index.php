<?php
define('PATH', realpath('../').'/');

//load the required classes
require(PATH.'libs/webFramework/basecontroller.php');
require(PATH.'libs/webFramework/basemodel.php');
require(PATH.'libs/webFramework/view.php');
//require(PATH.'libs/webFramework/viewmodel.php'); Pas besoin on utilise Smarty
require(PATH.'libs/webFramework/error.php');
require(PATH.'libs/webFramework/config.php');
require(PATH.'libs/webFramework/dbConnection.php');
require(PATH.'libs/webFramework/user.php');
require(PATH.'libs/webFramework/loader.php');

$loader = new Loader(); //create the loader object
$controller = $loader->createController(); //creates the requested controller object based on the 'controller' URL value
$controller->executeAction(); //execute the requested controller's requested method based on the 'action' URL value. Controller methods output a View.
?>