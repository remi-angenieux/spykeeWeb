<?php
class SpykeeController{
	/*
	 * Etats de l'action
	*/
	const STATE_OK = 1;
	const STATE_ERROR = 0;

	/*
	 * Protocole Controller
	*/

	// Actions
	const UNDEFINED_ACTION = 0;
	const TURN_LEFT = 1;
	const TURN_RIGHT = 2;
	const FORWARD = 3;
	const BACK = 4;
	const STOP = 5;
	const ACTIVATE = 6;
	const CHARGE_STOP = 7;
	const DOCK = 8;
	const DOCK_CANCEL = 9;
	const WIRELESS_NETWORKS = 10;
	const GET_LOG = 11;
	const GET_CONFIG = 12;
	const STOP_SERVER = 13;
	const GET_POWER_LEVEL = 14;
	const REFRESH_POWER_LEVEL = 15;
	const CONNECTION_TO_SERVER = 16;
	const SEND_MP3= 17;
	const AUDIO_PLAY= 18;
	const MOVE = 'MV';

	// Longueur des paquets
	const PAQUET_HEADER_SIZE = 7;

	/*
	 * Configuration
	*/
	const TIME_ZONE = 'Europe/Paris';
	const DEFAULT_USERNAME = 'admin';
	const DEFAULT_PASSWORD = 'admin';

	const MAX_CONNECTIONS = 10;
	const SERVER_FIRST_PORT = 2000; // Numero de port du premier server
	const SERVEUR_IP = '127.0.0.1';
	const CLIENT_IP = '127.0.0.1'; // Utilisé lors du flitrage des trames
	// 1 - Log juste les erreurs
	// 2 - Log les connexions
	// 3 - Log tout (erreurs, connexion, actions)
	const LOG_LEVEL = 3;

	const ROBOT_PORT = 9000;
	const LISTEN_TIME = 10000000; // Temps de scrutation des paquets en nanosecondes
}

?>