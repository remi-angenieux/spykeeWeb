<?php
require_once(PATH.'libs/spykee-robot/robot.php');
/*
 * Configuration de l'objet SpykeeClientRobot
 */
Class SpykeeConfigRobot extends SpykeeRobot{
	
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
	// Temps d'attente pour la connexion au robot
	const CONNECTION_ROBOT_TIMEOUT = 8;
	// Nombre maxmimum de reconnexion sucessive
	const NB_RECONNECTION = 5;
}
?>