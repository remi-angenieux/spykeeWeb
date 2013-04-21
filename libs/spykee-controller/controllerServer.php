<?php
// Pour que le script puisse tourner en serveur
set_time_limit(0);
if (!defined('PATH'))
	define('PATH', realpath('../../').'/');
require_once(PATH.'libs/spykee-robot/clientRobot.php');
require_once(PATH.'libs/spykee-controller/controller.php');

class SpykeeControllerServer{
	private static $_noController = 0;

	/*
	 * Attributs
	*/
	protected $_stopServer = false;
	protected $_logFile;
	protected $_SpykeeClientRobot;
	protected $_robotName;
	protected $_robotIp;
	protected $_serverPort;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_powerLevel = NULL; // NOTE : Non utilisé pour le moment

	function __construct($robotName, $robotIp, $serverPort='', $robotUsername=SpykeeController::DEFAULT_USERNAME, $robotPassword=SpykeeController::DEFAULT_PASSWORD){
		self::$_noController++;
		date_default_timezone_set(SpykeeController::TIME_ZONE); // Pour les dates des logs
		// TODO verifier que les ports sont disponibles
		$this->_serverPort = ($serverPort=='') ? ConnectionClient::SERVER_FIRST_PORT + self::$_noRobot : $serverPort;
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'-ControllerServer.log';

		$this->writeLog('Démarrage du controleur'."\r\n", 1);

		// Connexion au robot
		$this->_SpykeeClientRobot = new SpykeeClientRobot($this->_robotName, $this->_robotIp, $this->_robotUsername, $this->_robotPassword);

		// Ecoute des demandes d'actions
		$this->listenNetwork();
	}

	protected function writeLog($txt, $level=1){
		if (SpykeeController::LOG_LEVEL >= $level){
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

	protected function listenNetwork(){
		// Source : http://www.binarytides.com/php-socket-programming-tutorial/

		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible de créer le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}

		//echo "Socket created \r\n";

		// Bind the source address
		if( !socket_bind($sock, SpykeeController::SERVEUR_IP , $this->_serverPort) )		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible de lier le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}

		//echo "Socket bind OK \r\n";

		if(!socket_listen ($sock , SpykeeController::MAX_CONNECTIONS))		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			$this->writeLog('Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
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
			for ($i = 0; $i < SpykeeController::MAX_CONNECTIONS; $i++){
				if(!empty($client_socks[$i])){
					$read[$i+1] = $client_socks[$i];
				}
			}

			//now call select - blocking call
			$write=NULL;
			$except=NULL;
			if(socket_select($read, $write, $except, 0) === false){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
					
				$this->writeLog('Impossible d\'écouter le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
				die;
			}

			//if read contains the master socket, then a new connection has come in
			if (in_array($sock, $read)){
				for ($i = 0; $i < SpykeeController::MAX_CONNECTIONS; $i++){
					if (empty($client_socks[$i])){
						$client_socks[$i] = socket_accept($sock);
						/*
						 * Code exécuté lors de la connexion entre le client et le serveur
						*/

						// Filtrage IP
						socket_getpeername($client_socks[$i], $clientIp, $clientPort);
						if ($clientIp != SpykeeController::CLIENT_IP){
							$this->writeLog('Le client '.$clientIp.':'.$clientPort.' à tenté de se connecter mais à été rejetté par l\'ACL'."\r\n", 2);
							socket_close($client_socks[$i]);
							unset($client_socks[$i]);
						}
						else{
							$this->writeLog('Le client '.$clientIp.':'.$clientPort.' s\'est bien connecté '.$client_socks[$i].''."\r\n", 2);
							$msg = pack('a3CCn', 'CTR', SpykeeController::CONNECTION_TO_SERVER, SpykeeController::STATE_OK, 0);
							if( !socket_send($client_socks[$i], $msg, strlen($msg), 0)){
								$errorcode = socket_last_error();
								$errormsg = socket_strerror($errorcode);
									
								die("Could not send data: [$errorcode] $errormsg \n");
							}
						}

						// TODO Connexion TCP -> Session crée. On as besoin d'envoyer des données pour confirmer la connexion ?
						break;
					}
				}
			}

			//check each client if they send any data
			for ($i = 0; $i < SpykeeController::MAX_CONNECTIONS; $i++){
				if (!empty($client_socks[$i]) AND in_array($client_socks[$i] , $read)){
					/*
					 * Code exécuté
					*/
					$input = socket_read($client_socks[$i], 1024);

					// Si le client se déconnecte
					if ($input == null){
						//zero length string meaning disconnected, remove and close the socket
						socket_close($client_socks[$i]);
						unset($client_socks[$i]);
						$this->writeLog('Un client s\'est déconnecté'."\r\n", 2);
						// INFO : il n'est pas posible de connaitre l'ip d'un client qui se déconnecte
					}
					// Si le client à envoyer quelque chose à traiter
					else{
						socket_getpeername($client_socks[$i], $clientIp, $clientPort);
						$this->writeLog('Le client '.$clientIp.':'.$clientPort.' à envoyer au serveur : "'.bin2hex($input).'"'."\r\n", 3);
							
						/*
						 * Envoie au robot l'action demandé par le client
						*/
						/*if(preg_match('#^'.self::SERVER_MOVE.'([0-9]+):([0-9]+)#', $input, $move) == 1)
							$condMove = TRUE;
						else
							$condMove = FALSE;*/

						$responseType = $input; // Par défaut le type de réponse est celui qui à été demandé

						switch($input){
							case SpykeeController::TURN_LEFT:
								$response = $this->_SpykeeClientRobot->left();
								// TODO : Mettre à jour le niveau de batterie
								break;
							case SpykeeController::TURN_RIGHT:
								$response = $this->_SpykeeClientRobot->right();
								// TODO : Mettre à jour le niveau de batterie
								break;
							case SpykeeController::FORWARD:
								$response = $this->_SpykeeClientRobot->forward();
								// TODO : Mettre à jour le niveau de batterie
								break;
							case SpykeeController::BACK:
								$response = $this->_SpykeeClientRobot->back();
								// TODO : Mettre à jour le niveau de batterie
								break;
							case SpykeeController::STOP:
								$response = $this->_SpykeeClientRobot->stop();
								break;
							case SpykeeController::ACTIVATE:
								$response = $this->_SpykeeClientRobot->activate();
								break;
							case SpykeeController::CHARGE_STOP:
								$response = $this->_SpykeeClientRobot->charge_stop();
								break;
							case SpykeeController::DOCK:
								$response = $this->_SpykeeClientRobot->dock();
								break;
							case SpykeeController::DOCK_CANCEL:
								$response = $this->_SpykeeClientRobot->dock_cancel();
								break;
							case SpykeeController::WIRELESS_NETWORKS:
								$response = $this->_SpykeeClientRobot->wireless_networks();
								break;
							case SpykeeController::GET_LOG:
								$response = $this->_SpykeeClientRobot->get_log();
								break;
							case SpykeeController::GET_CONFIG:
								$response = $this->_SpykeeClientRobot->get_config();
								break;
							case SpykeeController::STOP_SERVER:
								$reponse = NULL;
								$this->_stopServer=true;
								foreach($client_socks as $key => $connection){
									socket_close($client_socks[$key]);
									unset($client_socks[$key]);
								}
								socket_close($sock);
								unset($sock);
								$this->writeLog('Le serveur à été éteint'."\r\n", 1);
								break;
							case SpykeeController::GET_POWER_LEVEL:
								$this->_powerLevel = $this->_SpykeeClientRobot->get_power_level();
								$response = $this->_powerLevel;
								break;
							case SpykeeController::REFRESH_POWER_LEVEL:
								$this->_powerLevel = $this->_SpykeeClientRobot->refresh_power_level();
								$response = $this->_powerLevel;
								break;
							case ((preg_match('#^'.SpykeeController::MOVE.'([0-9]+):([0-9]+)#', $input, $move)) ? $input : null) :
								$state = $this->_SpykeeClientRobot->move($move[1], $move[2]);
								break;

							default:
								$state = SpykeeController::STATE_ERROR;
								$reponse = NULL;
								$responseType = SpykeeController::UNDEFINED_ACTION;
								$this->writeLog('Trame inconnu : '.bin2hex($input).'"'."\r\n", 1);
								break;
						}

						/*
						 * Envoie au client la réponse du robot
						*/
						echo 'Reponse du Robot : '.$response."\r\n";
						if (!empty($response) AND ( $response === SpykeeController::STATE_OK OR $response === SpykeeController::STATE_ERROR )){
							$state = $response;
							$data=NULL;
						}
						elseif (!empty($response)){
							$state = SpykeeController::STATE_OK;
							$data=pack('C', $response);
						}
						else{
							$state = SpykeeController::STATE_ERROR;
							$data=NULL;
						}

						if(!$this->_stopServer){
							if (!empty($data))
								$responseLength = strlen($data);
							else
								$responseLength = 0;

							$reply = pack('a3CCn', 'CTR', $responseType, $state, $responseLength);
							if (!empty($data))
								$reply .= $data;
							if( !socket_send($client_socks[$i], $reply, strlen($reply), 0)){
								$errorcode = socket_last_error();
								$errormsg = socket_strerror($errorcode);
									
								die("Could not send data: [$errorcode] $errormsg \n");
							}
							echo 'Envoie de la trame : '.$reply."\r\n";
							$this->writeLog('Envoie de la trame : '.bin2hex($reply).'"'."\r\n", 3);
						}
					}
				}
			}
			time_nanosleep (0, SpykeeController::LISTEN_TIME);
		}
	}

	function __destruct(){
		self::$_noController--;
	}
}

?>
