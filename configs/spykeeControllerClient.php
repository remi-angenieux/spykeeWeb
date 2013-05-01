<?php
require_once(PATH.'libs/spykee-controller/controller.php');
/*
 * Configuration de l'objet SpykeeControllerClient
*/
Class SpykeeConfigRobot extends SpykeeController{
	
	// Temps d'attente maximum pour la connexion au controller
	const CONNECTION_CONTROLLER_TIMEOUT = 8;
}