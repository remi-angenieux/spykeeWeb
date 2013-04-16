<?php

// To run in daemon
set_time_limit(0);

class SpykeeServer{
	private static $_noRobot = 0;

	/*
	 * Actions
	*/
	const SERVER_TURN_LEFT = 1;
	const SERVER_TURN_RIGHT = 2;
	const SERVER_FORWARD = 3;
	const SERVER_BACK = 4;
	const SERVER_STOP = 5;
	const SERVER_STOP_SERVER = 13;
	const SERVER_MOVE = 'MV';
	
	/*
	 * SOCKET du robot
	 */
	// TODO voir si ça vaut pas le cout de changer de notation
	const ROBOT_HEADER_SIZE = 5;
	const ROBOT_DATA_SIZE_MAX = 32768; //32*1024
	
	const ROBOT_TYPE_AUDIO = 1;
	const ROBOT_TYPE_VIDEO = 2;
	const ROBOT_TYPE_POWER = 3;
	const ROBOT_TYPE_MOVE = 5;
	const ROBOT_TYPE_FILE =  6;
	const ROBOT_TYPE_PLAY = 7;
	const ROBOT_TYPE_STOP = 8;
	const ROBOT_TYPE_AUTH_REQUEST = 10;
	const ROBOT_TYPE_AUTH_REPLY = 11;
	const ROBOT_TYPE_CONFIG  = 13;
	const ROBOT_TYPE_WIRELESS_NETWORKS = 14;
	const ROBOT_TYPE_STREAMCTL = 15;
	const ROBOT_TYPE_ENGINE = 16;
	const ROBOT_TYPE_LOG = 17;
	

	/*
	 * Etats de l'action
	*/
	const SERVER_STATE_OK = 0;
	const SERVER_STATE_ERROR = 1;

	/*
	 * Configuration
	*/
	const TIMEZONE = 'Europe/Paris';
	const MAXCONNECTION = 10;
	const FIRSTPORT = 2000; // Numero de port du premier server
	const SERVEURIP = '127.0.0.1';
	const CLIENTIP = '127.0.0.1'; // Utilisé lors du flitrage des trames
	// 1 - Log juste les erreurs
	// 2 - Log les connexions
	// 3 - Log tout (erreurs, connexion, actions)
	const LOGLEVEL = 3;
	const ROBOTPORT = 9000;

	/*
	 * Définition des attributs
	*/
	private $_serverPort;
	private $_stopServer = false;
	private $_robotName;
	private $_robotUsername;
	private $_robotPassword;
	private $_robotIp;
	private $_logFile;
	private $_robotSocket;

	function __construct($robotName, $robotIp, $serverPort='', $robotUsername, $robotPassword){
		self::$_noRobot++;
		// TODO verifier que les ports sont disponibles
		$this->_serverPort = ($serverPort=='') ? self::FIRSTPORT + self::$_noRobot : $serverPort;
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'.log';
		$this->_robotIp = $robotIp;
		date_default_timezone_set(self::TIMEZONE);

		$this->connectionToTheRobot();

		$this->listenNetwork();
	}
	
	private function packString($str){
		return pack('Ca*', count($str), $str);
	}

	private function connectionToTheRobot(){
		
		/*
		 * Debut de la session TCP
		 */
		if(!($this->_robotSocket = socket_create(AF_INET, SOCK_STREAM, 0)))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
		
			$this->writeLog('(Robot) Impossible de créer le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}
		
		if( !socket_bind($this->_robotSocket, $this->_robotIp, self::ROBOTPORT) )
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
		
			$this->writeLog('(Robot) Impossible de lier le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}
		
		// Demande connexion au robot
		$this->sendPacketToRobot(self::ROBOT_TYPE_AUTH_REQUEST, $this->packString($this->_robotUsername).$this->packString($this->_robotPassword));

	}
	
	private function sendPacketToRobot($type, $data){
		$msg = pack('a2Cn', 'PK', $type);
		if( !socket_send($this->_robotSocket, $msg, count($msg), 0)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			
			$this->writeLog('(Robot) Impossible d\'envoyer le paquet : "'.$msg.'". ['.$errorCode.'] '.$errorMsg."\r\n", 1);
		 return FALSE;
		}
		return TRUE;
	}

	private function writeLog($txt, $level=1){
		if (self::LOGLEVEL >= $level){
			$content = date('[d/m/y] à H:i:s ', time());
			$content .= '@'.$this->_robotName.'('.$this->_robotIp.') : ';
			$content .= $txt;
	
			/*
			 * Création/Mise à jour du fichier de log
			*/
			if (!$file = fopen($this->_logFile, 'a')){
				echo 'Impossible d\'ouvrir le fichier : '.$this->_logFile;
			}
			else {
				if (fwrite($file, $content) === FALSE ){
					echo 'Impossible d\'écrire dans le fichier : '.$this->_logFile;
				}
				fclose($file);
			}
		}
	}

	private function listenNetwork(){
		// Source : http://www.binarytides.com/php-socket-programming-tutorial/

		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('(Serveur) Impossible de créer le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}

		//echo "Socket created \r\n";

		// Bind the source address
		if( !socket_bind($sock, self::SERVEURIP , $this->_serverPort) )		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('(Serveur) Impossible de lier le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}

		//echo "Socket bind OK \r\n";

		if(!socket_listen ($sock , self::MAXCONNECTION))		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('(Serveur) Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}

		//echo "Socket listen OK \r\n";

		//echo "Waiting for incoming connections... \r\n";

		//array of client sockets
		$client_socks = array();

		//array of sockets to read
		$read = array();

		//start loop to listen for incoming connections and process existing connections
		while (!$this->_stopServer){
			//prepare array of readable client sockets
			$read = array();

			//first socket is the master socket
			$read[0] = $sock;

			//now add the existing client sockets
			for ($i = 0; $i < self::MAXCONNECTION; $i++){
				if(!empty($client_socks[$i])){
					$read[$i+1] = $client_socks[$i];
				}
			}

			//now call select - blocking call
			
			if(socket_select($read) === false){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
					
				$this->writeLog('(Serveur) Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
				die;
			}

			//if read contains the master socket, then a new connection has come in
			if (in_array($sock, $read)){
				for ($i = 0; $i < self::MAXCONNECTION; $i++){
					if (empty($client_socks[$i])){
						$client_socks[$i] = socket_accept($sock);
						/*
						 * Code exécuté lors de la connexion entre le client et le serveur
						*/

						// Filtrage IP
						socket_getpeername($client_socks[$i], $clientIp);
						if ($clientIp != self::CLIENTIP){
							$this->writeLog('(Serveur) Le client '.$clientIp.' à tenté de se connecter mais à été rejetté par l\'ACL'."\r\n", 2);
							unset($client_socks[$i]);
							socket_close($client_socks[$i]);
						}
						else{
							socket_write($client_socks[$i], self::SERVER_STATE_OK);
							$this->writeLog('(Serveur) Le client '.$clientIp.' s\'est bien connecté '.$client_socks[$i].''."\r\n", 2);
						}

						// TODO Connexion TCP -> Session crée. On as besoin d'envoyer des données pour confirmer la connexion ?
						break;
					}
				}
			}

			//check each client if they send any data
			for ($i = 0; $i < self::MAXCONNECTION; $i++){
				if (!empty($client_socks[$i]) AND in_array($client_socks[$i] , $read)){
					/*
					 * Code exécuté
					*/
					$input = socket_read($client_socks[$i] , 1024);
					// Recupère l'ip du client
					socket_getpeername($client_socks[$i], $clientIp);

					// Suppression de la Session(connexion)
					if ($input == 13){
						//zero length string meaning disconnected, remove and close the socket
						socket_close($client_socks[$i]);
						$this->writeLog('(Serveur) Le client '.$clientIp.' s\'est déconnecté'."\r\n", 2);
					}

					$this->writeLog('(Serveur) Le client '.$clientIp.' à envoyer au serveur : "'.trim($input).'"'."\r\n", 3);
					
					// Fait l'action demandé
					switch($input){
						case self::SERVER_TURN_LEFT:
							$state = $this->turnLeft();
							break;
						case self::SERVER_TURN_RIGHT:
							$state = $this->turnRight();
							break;
						case self::SERVER_STOP_SERVER;
						$this->_stopServer=true;
						break;
						case (preg_match('/^'.self::SERVER_MOVE.'([0-9]):([0-9])/', $input, $matches) ? true : false):
							$state = $this->move($matches[1], $matches[2]);
							break;

						default:
							$state = self::SERVER_STATE_ERROR;
					}

					//send response to client
					socket_write($client_socks[$i] , $state);
				}
			}
		}
	}

	private function turnLeft(){

		return $this->move(140, 110);

	}

	private function turnRight(){
		
		return $this->move(110, 140);

	}

	private function move($left, $right){
		
		if ($this->sendPacketToRobot(self::ROBOT_TYPE_MOVE, pack('CC', $left, $right)))
			return self::SERVER_STATE_OK;
		else
			return self::SERVER_STATE_ERROR;
	}

	function __destruct(){
		self::$_noRobot--;
	}
}

?>
