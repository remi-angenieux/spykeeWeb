<?php
require_once(PATH.'libs/spykee-controller/controller.php');
/*
 * Configuration de l'objet SpykeeControllerServer
*/
Class SpykeeConfigControllerServer extends SpykeeController{
	
	/*
	 * Il n'est pas conseillé de modifier ces valeurs sans bien comprendre ce qu'elles font
	 * Cella peut empecher le bon fonctionnement du Controller
	 */
	// Temps entre l'envoie de chaque paquet "périodisé"
	const INTERVAL_SEND_HOLDING = 20; // 20 ms
	// Temps de scrutation des requêtes du client en ms
	const INTERVAL_LISTEN_CLIENT = 1;
	// Temps de scrutation des responses du robot en ms
	const INTERVAL_LISTEN_ROBOT = 1;
	// Interval de temps entre chaque vérification des timers exprimé en nanoseconde
	// Ce temps doit être strictement inférieur aux intervals des différents "Serveurs"
	const INTERVAL_WORK = 950000; // 0.95ms
	
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