<?php
require_once(PATH.'libs/spykee-controller/controller.php');
/*
 * Configuration de l'objet SpykeeControllerServer
*/
Class SpykeeConfigControllerServer extends SpykeeController{

	// Timezone utilisé pour les dates des logs
	const TIME_ZONE = 'Europe/Paris';
	// Login par défaut utilisé pour la connexion au robot
	const DEFAULT_USERNAME = 'admin';
	// Mot de passe par défaut utilisé pour la connexion au robot
	const DEFAULT_PASSWORD = 'admin';
	// 1 - Log juste les erreurs
	// 2 - Log les connexions
	// 3 - Log tout (erreurs, connexion, actions)
	const LOG_LEVEL = 3;
	// Nombre de connexion simultannée permise sur le controller
	const MAX_CONNECTIONS = 10;
	// Addresse IP du controller
	const CONTROLLER_IP = '127.0.0.1';
	// Addresse IP autorisé à entrer en contact avec le controller
	const CLIENT_IP = '127.0.0.1';
}
?>