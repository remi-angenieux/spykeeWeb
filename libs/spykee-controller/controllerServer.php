<?php
// Pour que le script puisse tourner en serveur
set_time_limit(0);
if (!defined('PATH'))
	define('PATH', realpath('../../').'/');
// Inclue la configuration du ControllerServer. Et ses constantes partagées
require_once(PATH.'configs/spykeeControllerServer.php');
// Inclue l'objet permettant de communiquer avec le robot
require_once(PATH.'libs/spykee-robot/clientRobot.php');
// Inclue l'objet utilisé lors des retours des différentes actions
require_once(PATH.'libs/spykee-robot/robotResponse.php');

class SpykeeControllerServer extends SpykeeConfigControllerServer{
	/*
	 * Attributs
	*/
	protected $_stopServer = false;
	protected $_logFile;
	protected $_SpykeeClientRobot;
	protected $_robotName;
	protected $_robotIp;
	protected $_controllerPort;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_powerLevel = NULL; // NOTE : Non utilisé pour le moment
	// Attributs pour la partie réception des requêtes client (Serveur)
	protected $_socketServer = NULL;
	protected $_socketsClient = array();
	// Attributs pour la partie envoie de paquet périodisé
	protected $_holdingQueue = array('left' => FALSE,
									'right' => FALSE,
									'forward' => FALSE,
									'back' => FALSE);
	

	function __construct($robotName, $robotIp, $serverPort, $robotUsername='', $robotPassword=''){
		date_default_timezone_set(self::TIME_ZONE); // Pour les dates des logs
		// TODO verifier que les ports sont disponibles
		$this->_controllerPort = $serverPort;
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'-ControllerServer.log';

		$this->writeLog('Démarrage du controleur'."\r\n", 1);

		// Connexion au robot
		$this->_SpykeeClientRobot = new SpykeeClientRobot($this->_robotName, $this->_robotIp, $this->_robotUsername, $this->_robotPassword);

		$this->initSocketServer();
		
		// Lance a proprement dit le serveur
		$this->mainLoop();
	}

	protected function writeLog($txt, $level=1){
		if (self::LOG_LEVEL >= $level){
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
	
	protected function mainLoop(){
		$timeClient=$timeRobot=$timePeriodic=microtime(true);
		while (!$this->_stopServer){
			if ( (microtime(true) - $timeClient) >= self::INTERVAL_LISTEN_CLIENT/100 ){
				$this->listenClientsRequests();
				$timeClient=microtime(true);
			}
			if ( (microtime(true) - $timeRobot) >= self::INTERVAL_LISTEN_ROBOT/100 ){
				$this->listenRobotResponses();
				$timeRobot=microtime(true);
			}
			if ( (microtime(true) - $timePeriodic) >= self::INTERVAL_SEND_HOLDING/100 ){
				$this->sendPeriodicPaquets();
				$timePeriodic=microtime(true);
			}
			time_nanosleep(0, self::INTERVAL_WORK);
		}
	}
	
	protected function initSocketServer(){
		if(!($this->_socketServer = socket_create(AF_INET, SOCK_STREAM, 0))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->writeLog('Impossible de créer le socket : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			die;
		}
		
		socket_set_option($this->_socketServer, SOL_SOCKET, SO_REUSEADDR, 1);
		
		//echo "Socket created \r\n";
		
		// Bind the source address
		if( !socket_bind($this->_socketServer, self::CONTROLLER_IP , $this->_controllerPort) )		{
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->writeLog('Impossible de lier le socket : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			die;
		}
		
		//echo "Socket bind OK \r\n";
		
		if(!socket_listen ($this->_socketServer , self::MAX_CONNECTIONS)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->writeLog('Impossible d\'écouter le socket : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			die;
		}
		
		$this->_socketsClient = array();
	}

	protected function listenClientsRequests(){
		// Source : http://www.binarytides.com/php-socket-programming-tutorial/

		//prepare array of readable client sockets
		$socketsClientToRead = array();
		//first socket is the master socket
		$socketsClientToRead[0] = $this->_socketServer;
		//now add the existing client sockets
		for ($i = 0; $i < self::MAX_CONNECTIONS; $i++){
			if(!empty($this->_socketsClient[$i]['socket'])){
				$socketsClientToRead[$i+1] = $this->_socketsClient[$i]['socket'];
			}
		}
		//now call select - blocking call
		$write=NULL;
		$except=NULL;
		if(socket_select($socketsClientToRead, $write, $except, 0, NULL) === false){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
				
			$this->writeLog('Impossible d\'écouter le socket : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			die;
		}
		// TODO Permettre la connexion simultanée de plusieurs clients en même temps
		//if read contains the master socket, then a new connection has come in
		if (in_array($this->_socketServer, $socketsClientToRead)){
			// Cherche un "slot" de connexion libre
			for ($i = 0; $i < self::MAX_CONNECTIONS; $i++){
				if (empty($this->_socketsClient[$i]['socket'])){
					$this->_socketsClient[$i]['socket'] = socket_accept($this->_socketServer);
					/*
					 * Code exécuté lors de la connexion entre le client et le serveur
					*/
					// Filtrage IP
					socket_getpeername($this->_socketsClient[$i]['socket'], $clientIp, $clientPort);
					if ($clientIp != self::CLIENT_IP){
						$this->writeLog('Le client '.$clientIp.':'.$clientPort.' à tenté de se connecter mais à été rejetté par l\'ACL'."\r\n", 2);
						socket_close($this->_socketsClient[$i]['socket']);
						unset($this->_socketsClient[$i]);
					}
					else{
						$this->writeLog('Le client '.$clientIp.':'.$clientPort.' s\'est bien connecté'."\r\n", 2);
						$this->_socketsClient[$i]['ip'] = $clientIp;
						$this->_socketsClient[$i]['port'] = $clientPort;
						$msg = pack('a3CCn', 'CTR', self::CONNECTION_TO_CONTROLLER, self::STATE_OK, 0);
						if( !socket_send($this->_socketsClient[$i]['socket'], $msg, strlen($msg), 0)){
							$errorCode = socket_last_error();
							$errorMsg = socket_strerror($errorCode);
								
							$this->writeLog('Impossible d\'envoie l\'accusé de récépetion (Connexion établie avec succès) : '.'['.$errorCode.'] '.$errorMsg."\r\n", 1);
						}
					}
					// On supprime le socket de la liste des socket à traiter
					unset($socketsClientToRead[array_search($this->_socketServer, $socketsClientToRead)]);
					break;
				}
				// Si tout les "slot" de connexion sont utilisés
				// On lui envoie quand même un message
				if ($i >= self::MAX_CONNECTIONS){
					$this->writeLog('Nombre maximum de connexion au controller atteint'."\r\n", 1);
					$TempSocket = socket_accept($this->_socketServer);
					$msg = pack('a3CCC', 'CTR', self::CONNECTION_TO_CONTROLLER, self::STATE_ERROR, SpykeeResponse::TOO_MANY_CONNECTION);
					if( !socket_send($TempSocket, $msg, strlen($msg), 0)){
						$errorCode = socket_last_error();
						$errorMsg = socket_strerror($errorCode);
					
						$this->writeLog('Impossible d\'envoie l\'accusé de récépetion (Nombre maxmimum de connexion atteint) : '.'['.$errorCode.'] '.$errorMsg."\r\n", 1);
					}
					socket_close($TempSocket);
					unset($TempSocket);
					// On supprime le socket de la liste des socket à traiter
					unset($socketsClientToRead[array_search($this->_socketServer, $socketsClientToRead)]);
				}
			}
		}
		//check each client if they send any data
		for ($i = 0; $i < self::MAX_CONNECTIONS; $i++)
		{
			if (!empty($this->_socketsClient[$i]) AND in_array($this->_socketsClient[$i]['socket'], $socketsClientToRead))
			{
				echo 'Nouvelle requête'."\r\n";
				/*
				 * Code exécuté
				*/
				$request = @socket_read($this->_socketsClient[$i]['socket'], self::CTR_PAQUET_HEADER_SIZE); // On lit la requête du client
				// Si le client se déconnecte
				if ($request == '' OR $request === false AND !is_resource($request)){
					//zero length string meaning disconnected, remove and close the socket
					socket_close($this->_socketsClient[$i]['socket']);
					$this->writeLog('Le client '.$this->_socketsClient[$i]['ip'].':'.$this->_socketsClient[$i]['port'].' s\'est déconnecté'."\r\n", 2);
					// On supprime le socket de la liste des sockets
					foreach($this->_socketsClient as $id => $value){
						if ($value['socket'] === $this->_socketsClient[$i]['socket']){
							unset($this->_socketsClient[$id]);
							break;
						}
					}
					return TRUE;
					// INFO : il n'est pas posible de connaitre l'ip d'un client qui se déconnecte
				}
				// Si le client à envoyer quelque chose à traiter
				else{
					// TODO utiliser la fonction unpack
					$request = bin2hex($request);
					$header = hex2bin($request[0].$request[1].$request[2].$request[3].$request[4].$request[5]);
					$type = base_convert($request[6].$request[7], 16, 10);
					$state = base_convert($request[8].$request[9], 16, 10);
					$idDescription = base_convert($request[10].$request[11], 16, 10);
					$length = base_convert($request[12].$request[13].$request[14].$request[15], 16, 10);
					// Récupère les données enventuellement envoyés
					$input = (!empty($length) AND $length>0) ? socket_read($this->_socketsClient[$i]['socket'], $length) : null;
					$this->writeLog('Le client '.$this->_socketsClient[$i]['ip'].':'.$this->_socketsClient[$i]['port'].' à envoyer au serveur : "'.$request.bin2hex($input).'"'."\r\n", 3);
					echo 'Paquet reçue : header='.$header.', type='.$type.', state ='.$state.', idDesc ='.$idDescription.', len='.$length."\r\n";
					echo 'Data reçue : '.bin2hex($input)."\r\n";	
					/*
					 * Envoie au robot l'action demandé par le client
					*/
					echo "\r\n\r\n$type\r\n\r\n";
					$responseType = $type; // Par défaut le type de réponse est celui qui à été demandé
					switch($type){
					case self::MOVE:
						$inputFormated = unpack('Cleft/Cright', $input);
						$state = $this->_SpykeeClientRobot->move($inputFormated['left'], $inputFormated['right']);
						$response = NULL;
						break;
					case self::LEFT:
						$this->_SpykeeClientRobot->left();
						$response = NULL;
						break;
					case self::RIGHT:
						$this->_SpykeeClientRobot->right();
						$response = NULL;
						break;
					case self::FORWARD:
						$this->_SpykeeClientRobot->forward();
						$response = NULL;
						break;
					case self::BACK:
						$this->_SpykeeClientRobot->back();
						$response = NULL;
						break;
					case self::STOP:
						// On stop toutes les actions en holding
						$this->_holdingQueue['left'] = false;
						$this->_holdingQueue['right'] = false;
						$this->_holdingQueue['forward'] = false;
						$this->_holdingQueue['back'] = false;
						$this->_SpykeeClientRobot->stop();
						$response = NULL;
						break;
					case self::ACTIVATE:
						$response = $this->_SpykeeClientRobot->activate();
						break;
					case self::CHARGE_STOP:
						$response = $this->_SpykeeClientRobot->chargeCtop();
						break;
					case self::DOCK:
						$response = $this->_SpykeeClientRobot->dock();
						break;
					case self::DOCK_CANCEL:
						$response = $this->_SpykeeClientRobot->dockCancel();
						break;
					case self::WIRELESS_NETWORKS:
						$response = $this->_SpykeeClientRobot->wirelessNetworks();
						break;
					case self::GET_LOG:
						$response = $this->_SpykeeClientRobot->getLog();
						break;
					case self::SEND_MP3:
						// TODO finir send MP3
						$response = $this->_SpykeeClientRobot->sendMp3('./../music/music.mp3');
						break;
					case self::GET_CONFIG:
						$response = $this->_SpykeeClientRobot->getConfig();
						break;
					case self::AUDIO_PLAY:
						// TODO Finir audio play
						$response = $this->_SpykeeClientRobot->audioPlay('./../music/music.mp3');
						break;
					case self::VIDEO:
						$inputFormated = unpack('Cstate', $input);
						$this->_SpykeeClientRobot->setVideo($inputFormated['state']);
						$response = NULL;
						break;
					case self::STOP_SERVER:
						$reponse = NULL;
						$this->_stopServer=true;
						// Ferme toutes les connexions
						foreach($this->_socketsClient as $key => $connection){
							socket_close($connection['socket']);
							unset($this->_socketsClient[$key]);
						}
						socket_close($this->_socketServer);
						unset($this->_socketServer);
						$this->writeLog('Le serveur à été éteint'."\r\n", 1);
						return TRUE; // On arrète complètement le script
						break;
					case self::GET_POWER_LEVEL:
						if ($this->_powerLevel != NULL)
							$response = new SpykeeResponse(self::STATE_OK, SpykeeResponse::LEVEL_BATTERY_RETRIVED, $this->_powerLevel);
						else{
							$response = $this->_SpykeeClientRobot->getPpowerLlevel();
						}
						break;
					case self::REFRESH_POWER_LEVEL:
						$request = $this->_SpykeeClientRobot->refreshPpowerLlevel();
						if ($request->getState() == self::STATE_OK){
							$response = $request;
							$this->_powerLevel = $request->getData();
						}
						else
							$response = $request;
						break;
					case self::HOLDING_LEFT:
						$this->holdingLeft();
						$response = NULL;
						break;
					case self::HOLDING_RIGHT:
						$this->holdingRight();
						$response = NULL;
						break;
					case self::HOLDING_FORWARD:
						$this->holdingForward();
						$response = NULL;
						break;
					case self::HOLDING_BACK:
						$this->holdingBack();
						$response = NULL;
						break;
					case self::STOP_HOLDING_LEFT:
						$this->stopHoldingLeft();
						$response = NULL;
						break;
					case self::STOP_HOLDING_RIGHT:
						$this->stopHoldingRight();
						$response = NULL;
						break;
					case self::STOP_HOLDING_FORWARD:
						$this->stopHoldingForward();
						$response = NULL;
						break;
					case self::STOP_HOLDING_BACK:
						$this->stopHoldingBack();
						$response = NULL;
						break;
						
					default:
						$response = new SpykeeResponse(self::STATE_ERROR, 'Requête iconnue');
						$this->writeLog('Trame inconnu : '.$request.bin2hex($input).'"'."\r\n", 1);
						break;
					}
					/*
					 * Envoie au client la réponse du robot
					*/
					if ($response != NULL){ // Si il faut envoyer un paquet au client
						if(!$response->getData()){
							$data=pack('C', $response->getData());
							$dataLength = strlen($data);
						}
						else
							$dataLength = 0;
						$reply = pack('a3CCCn', 'CTR', $responseType, $response->getState(), $response->getIdDescription(), $dataLength);
						if (!$response->getData())
							$reply .= $data;
						if( !socket_send($this->_socketsClient[$i]['socket'], $reply, strlen($reply), 0)){
							$errorCode = socket_last_error();
							$errorMsg = socket_strerror($errorCode);
								
							$this->writeLog('Impossible d\'envoyer une trame au client : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
						}
						echo 'Envoie de la trame : '.$reply."\r\n";
						$this->writeLog('Envoie de la trame : '.bin2hex($reply).'"'."\r\n", 3);
					}
				}
			}
		}
	}
	
	/*
	 * Récupère les réponses du robot comme l'état de batterie ou le stream audio/video
	 */
	protected function listenRobotResponses(){
		$result = $this->_SpykeeClientRobot->socketHook();
		// Si aucune trame n'a été capturée
		if(!is_object($result))
			return FALSE;
			
		// Si la fonction à retourner une image du stream vidéo
		if ($result->getIdDescription() == SpykeeResponse::RECEIVE_PAQUET_TYPE_VIDEO){
			$file = PATH.'www/videoStream/video.jpeg';
			if (file_put_contents($file, $result->getData()) === false){
				echo 'Problème dans l\'enregistrement de l\'image'."\r\n";
				$this->writeLog('Impossible d\'enregistrer une image du stream vidéo. ('.$file.')'."\r\n", 1);
			}
		}
		// Si la fonction à retourner le niveau de batterie
		elseif ($result->getIdDescription() == SpykeeResponse::RECEIVE_PAQUET_TYPE_POWER){
			$this->_powerLevel = $result->getData(); 
		}
	}
	
	/*
	 * Envoie les paquets qui doivent êtres envoyés à intervals réguliers
	 */
	protected function sendPeriodicPaquets(){
		if ($this->_holdingQueue['left'])
			$this->_SpykeeClientRobot->left();
		if ($this->_holdingQueue['right'])
			$this->_SpykeeClientRobot->right();
		if ($this->_holdingQueue['forward'])
			$this->_SpykeeClientRobot->forward();
		if ($this->_holdingQueue['back'])
			$this->_SpykeeClientRobot->back();
	}
	
	protected function holdingLeft(){
		if ($this->_holdingQueue['right'])
			$this->_holdingQueue['right'] = false;
		$this->_holdingQueue['left'] = true;
	}
	
	protected function holdingRight(){
		if ($this->_holdingQueue['left'])
			$this->_holdingQueue['left'] = false;
		$this->_holdingQueue['right'] = true;
	}
	
	protected function holdingForward(){
		if ($this->_holdingQueue['back'])
			$this->_holdingQueue['back'] = false;
		$this->_holdingQueue['forward'] = true;
	}
	
	protected function holdingBack(){
		if ($this->_holdingQueue['forward'])
			$this->_holdingQueue['forward'] = false;
		$this->_holdingQueue['back'] = true;
	}
	
	protected function stopHoldingLeft(){
		$this->_holdingQueue['left'] = false;
		$this->_SpykeeClientRobot->stop();
	}
	
	protected function stopHoldingRight(){
		$this->_holdingQueue['right'] = false;
		$this->_SpykeeClientRobot->stop();
	}
	
	protected function stopHoldingForward(){
		$this->_holdingQueue['forward'] = false;
		$this->_SpykeeClientRobot->stop();
	}
	
	protected function stopHoldingBack(){
		$this->_holdingQueue['back'] = false;
		$this->_SpykeeClientRobot->stop();
	}
}

?>
