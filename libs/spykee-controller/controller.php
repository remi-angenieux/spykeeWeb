<?php
class SpykeeController{
	/*
	 * Etats de l'action
	*/
	const STATE_OK = 1;
	const STATE_ERROR = 0;
	
	/*
	 * Configuration
	 */
	const TIME_ZONE = 'Europe/Paris';
	
	const MAX_CONNECTIONS = 10;
	const SERVER_FIRST_PORT = 2000; // Numero de port du premier server
	const SERVEUR_IP = '127.0.0.1';
	const CLIENT_IP = '127.0.0.1'; // Utilisé lors du flitrage des trames
	// 1 - Log juste les erreurs
	// 2 - Log les connexions
	// 3 - Log tout (erreurs, connexion, actions)
	const LOG_LEVEL = 3;
	
	const ROBOT_PORT = 9000;
} 

?>