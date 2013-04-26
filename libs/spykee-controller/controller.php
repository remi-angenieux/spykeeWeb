<?php
require_once(PATH.'libs/spykee-robot/robot.php');
/*
 * Constantes mise en commun avec le Client du Controller
 */
class SpykeeController extends SpykeeRobot{
	/*
	 * Protocole Controller
	*/

	// Actions
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
}

?>