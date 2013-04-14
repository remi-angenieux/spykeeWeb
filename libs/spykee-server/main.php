<?php

// To run in daemon
set_time_limit(0);

class SpykeeServer{
	private static $_noRobot = 0;

	/*
	 * Actions
	*/
	const TURNLEFT = 1;
	const TURNRIGHT = 2;
	const STOPSERVER = 13;

	/*
	 * Etats de l'action
	*/
	const STATEOK = 0;
	const STATEERROR = 1;

	/*
	 * Configuration
	*/
	const TIMEZONE = 'Europe/Paris';
	const MAXCONNECTION = 10;
	const FIRSTPORT = 2000; // Numero de port du premier server
	const SERVEURIP = '127.0.0.1';
	const CLIENTIP = '127.0.0.1'; // Utilisé lors du flitrage des trames

	/*
	 * Définition des attributs
	*/
	private $_serverPort;
	private $_stopServer = false;
	private $_robotName;
	private $_robotIp;
	private $_logFile;

	function __construct($robotName, $robotIp, $serverPort=''){
		self::$_noRobot++;
		// TODO verifier que les ports sont disponibles
		$this->_serverPort = ($serverPort=='') ? self::FIRSTPORT + self::$_noRobot : $serverPort;
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_logFile = realpath(__FILE__).'../../logs/'.$this->_robotName.'.log';
		$this->_robotIp = $robotIp;
		date_default_timezone_set(self::TIMEZONE);

		$this->connectionToTheRobot();

		$this->listenNetwork();
	}

	private function connectionToTheRobot(){

	}

	private function writeLog($txt){
		$content = date('[d/m/y] à H:i:s ', time());
		$content .= '@'.$this->_robotName.'('.$this->_robotIp.') ';
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

	private function listenNetwork(){
		// Source : http://www.binarytides.com/php-socket-programming-tutorial/

		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible de créer le socket : ['.$errorcode.'] '.$errormsg."\n");
			die;
		}

		//echo "Socket created \n";

		// Bind the source address
		if( !socket_bind($sock, self::SERVEURIP , $this->_serverPort) )
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible de lier le socket : ['.$errorcode.'] '.$errormsg."\n");
			die;
		}

		//echo "Socket bind OK \n";

		if(!socket_listen ($sock , self::MAXCONNECTION))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\n");
			die;
		}

		//echo "Socket listen OK \n";

		//echo "Waiting for incoming connections... \n";

		//array of client sockets
		$client_socks = array();

		//array of sockets to read
		$read = array();

		//start loop to listen for incoming connections and process existing connections
		while (!$this->_stopServer)
		{
			//prepare array of readable client sockets
			$read = array();

			//first socket is the master socket
			$read[0] = $sock;

			//now add the existing client sockets
			for ($i = 0; $i < self::MAXCONNECTION; $i++)
			{
				if($client_socks[$i] != null){
					$read[$i+1] = $client_socks[$i];
				}
			}

			//now call select - blocking call
			// TODO $write n'est pas utilise, à supprimer
			if(socket_select($read , $write , $except , null) === false){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
					
				$this->writeLog('Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\n");
				die;
			}

			//if read contains the master socket, then a new connection has come in
			if (in_array($sock, $read)){
				for ($i = 0; $i < self::MAXCONNECTION; $i++){
					if ($client_socks[$i] == null){
						$client_socks[$i] = socket_accept($sock);
						/*
						 * Code exécuté lors de la connexion entre le client et le serveur
						*/

						// Filtrage IP
						socket_getpeername($client_socks[$i], $clientIp);
						if ($clientIp != self::CLIENTIP){
							unset($client_socks[$i]);
							socket_close($client_socks[$i]);
						}

						// TODO Connexion TCP -> Session crée. On as besoin d'envoyer des données pour confirmer la connexion ?
						break;
					}
				}
			}

			//check each client if they send any data
			for ($i = 0; $i < self::MAXCONNECTION; $i++){
				if (in_array($client_socks[$i] , $read)){
					/*
					 * Code exécuté
					*/
					$input = socket_read($client_socks[$i] , 1024);

					// Suppression de la Session(connexion)
					if ($input == null){
						//zero length string meaning disconnected, remove and close the socket
						unset($client_socks[$i]);
						socket_close($client_socks[$i]);
					}

					// Fait l'action demandé
					switch($input){
						case self::TURNLEFT:
							$state = $this->turnLeft();
							break;
						case self::TURNRIGHT:
							$state = $this->turnRight();
							break;
						case self::STOPSERVER;
							$this->_stopServer=true;
							break;
							
						default:
							$state = self::STATEERROR;
					}

					//send response to client
					socket_write($client_socks[$i] , $state);
				}
			}
		}
	}

	private function turnLeft(){

		return $state;
	}

	private function turnRight(){

		return $state;
	}

	function __destruct(){
		self::$_noRobot--;
	}
}

?>
