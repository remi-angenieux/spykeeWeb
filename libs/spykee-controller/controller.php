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
	const MOVE = 0;
	const LEFT = 1;
	const RIGHT = 2;
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
	const CONNECTION_TO_CONTROLLER = 16;
	const SEND_MP3= 17;
	const AUDIO_PLAY= 18;
	
	const HOLDING_LEFT = 20;
	const HOLDING_RIGHT = 21;
	const HOLDING_FORWARD = 22;
	const HOLDING_BACK = 23;
	const STOP_HOLDING_LEFT = 24;
	const STOP_HOLDING_RIGHT = 25;
	const STOP_HOLDING_FORWARD = 26;
	const STOP_HOLDING_BACK = 27;

	// Longeur de l'entête du protocole de controller (ControllerClient <-> ControllerServer)
	const CTR_PAQUET_HEADER_SIZE = 8;
}

?>