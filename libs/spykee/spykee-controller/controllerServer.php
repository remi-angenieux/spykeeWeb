<?php
/**
 * Content all methodes used by the controller
 * @author Remi ANGENIEUX
 */
set_time_limit(0); // to run PHP as server
if (!defined('PATH'))
	define('PATH', realpath('../../').'/');
// Includes shared constants
require_once(PATH.'libs/spykee/spykee-controller/controller.php');
// Includes the object used to communicate with the robot
require_once(PATH.'libs/spykee/spykee-robot/robotClient.php');
// Includes the object used in the returns of the different robot actions
require_once(PATH.'libs/spykee/response.php');

class SpykeeControllerServer extends SpykeeController{
	protected $_stopServer = false;
	protected $_SpykeeRobotClient;
	protected $_robotName;
	protected $_robotIp;
	protected $_controllerPort;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_powerLevel = NULL;
	protected $_moveSpeed;
	protected $_config;
	protected $_errorManager;
	// Sockets
	protected $_socketServer = NULL;
	protected $_socketsClient = array();
	// Attributes used to send packets periodically
	protected $_holdingQueue = array('left' => FALSE,
									'right' => FALSE,
									'forward' => FALSE,
									'back' => FALSE);
	const REQUEST_PACKET=0;
	const RESPONSE_PACKET=1;

	/**
	 * Create a new controller server connected to the robot
	 * @param string $robotName
	 * @param string $robotIp
	 * @param string $robotUsername
	 * @param string $robotPassword
	 */
	function __construct($robotName, $robotIp, $controllerPort, $robotUsername='', $robotPassword=''){
		$this->_robotName = $robotName;
		$this->_setRobotIp($robotIp); // Control the input
		$this->_setControllerPort($controllerPort);
		try{
			$this->_config = new SpykeeConfig('spykeeControllerServer.ini'); // Load config
		}
		catch (SpykeeException $e){ // If an error ocurred at the init of the config object
			SpykeeError::standaloneError($e->getMessage());
			die; // Stop the controller
		}
		$this->_errorManager = new SpykeeError($this->_robotName, $this->_robotIp, $this->_config);
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_moveSpeed = $this->_config->robot->defaultSpeed;
		$this->_errorManager->writeLog('Starting Controller', 1);
		// Connect to the robot
		try{
			$this->_SpykeeRobotClient = new SpykeeRobotClient($this->_robotName, $this->_robotIp, $this->_robotUsername, $this->_robotPassword);
		}
		catch (SpykeeException $e){
			$this->_errorManager->error($e->getMessage(), 1);
			die; // Stop the controller
		}
		$this->_initSocketServer();
		
		// Set the default speed
		$this->_SpykeeRobotClient->setSpeed($this->_moveSpeed);
		
		// Launch the server
		$this->_mainLoop();
	}
	
	/**
	 * Verify if the input value is an IP addresse otherwise generate an error
	 * @param string $ip
	 */
	protected function _setRobotIp($ip){
		if (filter_var($ip, FILTER_VALIDATE_IP)) // If the use enter a valid IP addresse
			$this->_robotIp = $ip;
		else{
			// Send error with 2 methodes because it's critical programming error
			// And kill the script
			$trace = debug_backtrace();
			$errorMessage = 'Argument 2 for SpykeeControllerServer::__construct() have to be an valid IP addresse, called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			SpykeeError::standaloneError($errorMessage);
		}
	}
	
	/**
	 * Detect if the input value is port addresse otherwise generate an error
	 * @param mixed $port
	 */
	protected function _setControllerPort($port){
		if (is_numeric($port) AND $port > 0 AND $port <= 49151)
			$this->_controllerPort = $port;
		else{
			// Send error with 2 methodes because it's critical programming error
			// And kill the script
			$trace = debug_backtrace();
			$errorMessage = 'Argument 3 for SpykeeControllerServer::__construct() have to be an valid port addresse (between 1 and 49151 included), called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			SpykeeError::standaloneError($errorMessage);
		}
	}
	
	/**
	 * Server loop
	 */
	protected function _mainLoop(){
		$timeClient=$timeRobot=$timePeriodic=microtime(true);
		while (!$this->_stopServer){
			if ( (microtime(true) - $timeClient) >= $this->_config->intervals->listenClient/100 ){
				$this->_listenClientsRequests();
				$timeClient=microtime(true);
			}
			if ( (microtime(true) - $timeRobot) >= $this->_config->intervals->listenRobot/100 ){
				$this->_listenRobotResponses();
				$timeRobot=microtime(true);
			}
			if ( (microtime(true) - $timePeriodic) >= $this->_config->intervals->holding/100 ){
				$this->_sendPeriodicPaquets();
				$timePeriodic=microtime(true);
			}
			time_nanosleep(0, $this->_config->intervals->work);
		}
	}
	
	/**
	 * Init controller server socket
	 */
	protected function _initSocketServer(){
		if(!($this->_socketServer = socket_create(AF_INET, SOCK_STREAM, 0))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			$this->_errorManager->error('Launching error', 'Could not create socket: ['.$errorCode.'] '.$errorMsg, 1);
			die; // Stop the controller
		}
		// Define reusable socket (Without you can't restart instantly the controller)
		if(!socket_set_option($this->_socketServer, SOL_SOCKET, SO_REUSEADDR, 1)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			$this->_errorManager->error('Unable to define controller socket reusable: ['.$errorCode.'] '.$errorMsg, 1);
			die; // Stop the controller
		}
		// Defines a timeout for receiving packet
		if(!socket_set_option($this->_socketServer, SOL_SOCKET, SO_RCVTIMEO, array('sec'=> $this->_config->controller->timeoutSec, 'usec'=> $this->_config->controller->timeoutUsec))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			$this->_errorManager->error('Unable to define timeout for controller socket: ['.$errorCode.'] '.$errorMsg, 1);
		}
		// Bind the source address. Packet send by everyone (localhost, local network, internet)
		// Note: An ACL secure the connection for each connection
		if( !socket_bind($this->_socketServer, '127.0.0.1' , $this->_controllerPort) ){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			if($errorCode == 98) // Port in use
				$this->_errorManager->error('Bind Socket: ['.$errorCode.'] Port in use('.$errorMsg.')', 1);
			else
				$this->_errorManager->error('Bind Socket: ['.$errorCode.'] '.$errorMsg, 1);
			die; // Stop the controller
		}
		
		if(!socket_listen($this->_socketServer, $this->_config->controller->maxConnection)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			$this->_errorManager->error('Unable to listen socket (socket_listen): ['.$errorCode.'] '.$errorMsg, 1);
			die; // Stop the controller
		}
		
		$this->_socketsClient = array(); // list of Controller users
	}

	/**
	 * Listen clients request and response to him
	 * @return boolean
	 */
	protected function _listenClientsRequests(){
		// Helpful : http://www.binarytides.com/php-socket-programming-tutorial/

		// Prepare array of readable client sockets
		$socketsClientToRead = array();
		// set the master socket to listen
		$socketsClientToRead[0] = $this->_socketServer;
		// Add client socket
		for ($i = 0; $i < $this->_config->controller->maxConnection; $i++){
			if(!empty($this->_socketsClient[$i]['socket']))
				$socketsClientToRead[$i+1] = $this->_socketsClient[$i]['socket'];
		}
		$write=NULL;
		$except=NULL;
		if(socket_select($socketsClientToRead, $write, $except, 0, NULL) === false){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			$this->_errorManager->error('Unable to listen socket (socket_listen): ['.$errorCode.'] '.$errorMsg, 1);
			die; // Stop the controller
		}
		/*
		 * Script executed at the connection of an host
		 * If read socket contains the master socket, then a new connection has come in
		 */ 
		while (in_array($this->_socketServer, $socketsClientToRead)){
			// Search an empty slot
			for ($i = 0; $i < $this->_config->controller->maxConnection; $i++){
				if (empty($this->_socketsClient[$i]['socket'])){ // An empty slot is available
					$this->_socketsClient[$i]['socket'] = socket_accept($this->_socketServer);
					/*
					 * Code executed when the connection between the client and the server is made
					 */
					// Get IP addresse of the host. Useful for ACL
					socket_getpeername($this->_socketsClient[$i]['socket'], $clientIp, $clientPort);
					if (!in_array($clientIp, $this->_config->controller->ipAllowed)){
						$this->_errorManager->writeLog('The host '.$clientIp.':'.$clientPort.' has attempted to connect to the controller but been reject by the ACL', 2);
						socket_close($this->_socketsClient[$i]['socket']);
						unset($this->_socketsClient[$i]); // This socket is close, remove of active user list
					}
					else{
						$this->_errorManager->writeLog('The host '.$clientIp.':'.$clientPort.' is well connected', 2);
						$this->_socketsClient[$i]['ip'] = $clientIp; // Save ip addresse
						$this->_socketsClient[$i]['port'] = $clientPort; // Save port addresse
					}
					// Remove the socket from the list of socket processing
					unset($socketsClientToRead[array_search($this->_socketServer, $socketsClientToRead)]);
					break;
				}
				// If all slot are busy. We still send a message
				if ($i >= $this->_config->controller->maxConnection){
					$this->_errorManager->writeLog('All server slot are busy', 2);
					$TempSocket = socket_accept($this->_socketServer);
					// Send the packet to the host
					$this->_sendPacketToClient($TempSocket, self::CONNECTION_TO_CONTROLLER, self::STATE_ERROR, SpykeeResponse::TOO_MANY_CONNECTION);
					socket_close($TempSocket);
					unset($TempSocket);
					// Remove this socket of list socket to manage
					unset($socketsClientToRead[array_search($this->_socketServer, $socketsClientToRead)]);
				}
			}
		}
		/*
		 * Check each client if they send any data
		 */
		for ($i = 0; $i < $this->_config->controller->maxConnection; $i++){
			// If a client send any data
			if (!empty($this->_socketsClient[$i]) AND in_array($this->_socketsClient[$i]['socket'], $socketsClientToRead)){
				// Get socket
				socket_clear_error();
				$request = @socket_read($this->_socketsClient[$i]['socket'], self::CTR_PACKET_REQUEST_HEADER_SIZE);
				// If an error occured
				if (($errorCode = socket_last_error()) != 0){
					$errorMsg = socket_strerror($errorCode);
					$this->_errorManager->error('Unable to receive packet: ['.$errorCode.'] '.$errorMsg, 1);
					// If we can't read data, we can't do anything with him so we disconnect him
					$this->_closeClientSocket($i);
					return FALSE;
				}
				// If the client want to disconnect
				if ($request == '' OR $request === false){
					$this->_closeClientSocket($i);
					return TRUE;
				}
				// If the client to send something to manage
				else{
					$header = unpack('a3header/Ctype/nlength', $request);
					// If header isn't CTR, the packet isn't send by valid host
					if ($header['header'] != 'CTR'){
						$this->_errorManager->error('packet receive without the correct header: '.$header['header'], 1);
						$this->_closeSocket();
						return FALSE;
					}
					if (!empty($header['length']) AND $header['length'] > 0){
						if (@socket_recv($this->_socketsClient[$i]['socket'], $input, $header['length'], MSG_WAITALL) === false){
							$errorCode = socket_last_error();
							$errorMsg = socket_strerror($errorCode);
							$this->_errorManager->error('Unable to read data sended: ['.$errorCode.'] '.$errorMsg, 1);
							// If we can't read data, we can't do anything with him so we disconnect him
							$this->_closeClientSocket($i);
							return FALSE;
						}
					}
					else
						$input=NULL;
					$packetString = $this->_packetToString($request.$input, self::REQUEST_PACKET);
					//if (!empty($input)) $packetString .= '/Data:'.bin2hex($input);
					$this->_errorManager->writeLog('Host '.$this->_socketsClient[$i]['ip'].':'.$this->_socketsClient[$i]['port'].' sent to the server: '.$packetString, 3);
					/*
					 * Send action asked by the host to the robot
					*/
					$responseType = $header['type']; // By default, the type of response that is requested
					switch($header['type']){
					case self::MOVE:
						$inputFormated = unpack('Cleft/Cright', $input);
						$state = $this->_SpykeeRobotClient->move($inputFormated['left'], $inputFormated['right']);
						$response = NULL;
						break;
					case self::LEFT:
						$this->_SpykeeRobotClient->left();
						$response = NULL;
						break;
					case self::RIGHT:
						$this->_SpykeeRobotClient->right();
						$response = NULL;
						break;
					case self::FORWARD:
						$this->_SpykeeRobotClient->forward();
						$response = NULL;
						break;
					case self::BACK:
						$this->_SpykeeRobotClient->back();
						$response = NULL;
						break;
					case self::STOP:
						// Stop all holding actions
						$this->_holdingQueue['left'] = false;
						$this->_holdingQueue['right'] = false;
						$this->_holdingQueue['forward'] = false;
						$this->_holdingQueue['back'] = false;
						$this->_SpykeeRobotClient->stop();
						$response = NULL;
						break;
					case self::ACTIVATE:
						$response = $this->_SpykeeRobotClient->activate();
						break;
					case self::CHARGE_STOP:
						$response = $this->_SpykeeRobotClient->chargeCtop();
						break;
					case self::DOCK:
						$response = $this->_SpykeeRobotClient->dock();
						break;
					case self::DOCK_CANCEL:
						$response = $this->_SpykeeRobotClient->dockCancel();
						break;
					case self::WIRELESS_NETWORKS:
						$response = $this->_SpykeeRobotClient->wirelessNetworks();
						break;
					case self::GET_LOG:
						$response = $this->_SpykeeRobotClient->getLog();
						break;
					/*case self::SEND_MP3:
						// TODO finir send MP3
						$response = $this->_SpykeeRobotClient->sendMp3('./../music/music.mp3');
						break;*/
					case self::GET_CONFIG:
						$response = $this->_SpykeeRobotClient->getConfig();
						break;
					/*case self::AUDIO_PLAY:
						// TODO Finir audio play
						$response = $this->_SpykeeRobotClient->audioPlay('./../music/music.mp3');
						break;*/
					case self::VIDEO:
						$inputFormated = unpack('Cvideo', $input);
						$this->_SpykeeRobotClient->setVideo($inputFormated['video']);
						$response = NULL;
						break;
					case self::STOP_CONTROLLER:
						$reponse = NULL;
						$this->_stopServer=true;
						// Close all connection
						foreach($this->_socketsClient as $key => $connection){
							socket_close($connection['socket']);
							unset($this->_socketsClient[$key]);
						}
						socket_close($this->_socketServer);
						unset($this->_socketServer);
						$this->_errorManager->writeLog('The controller was shutdown', 1);
						return TRUE;
						break;
					case self::GET_POWER_LEVEL:
						if ($this->_powerLevel != NULL) // if the battery level isn't stored
							$response = new SpykeeResponse(self::STATE_OK, SpykeeResponse::LEVEL_BATTERY_RETRIVED, $this->_powerLevel);
						else{
							$response = $this->_SpykeeRobotClient->refreshPowerLevel(); // Get the real level
							if ($response->getState() == self::STATE_OK)
								$this->_powerLevel = $response->getData();
						}
						break;
					case self::REFRESH_POWER_LEVEL:
						$response = $this->_SpykeeRobotClient->refreshPowerLevel();
						if ($request->getState() == self::STATE_OK)
							$this->_powerLevel = $request->getData();
						break;
					case self::GET_SPEED:
						$response = new SpykeeResponse(self::STATE_OK, SpykeeResponse::MOVE_SPEED_RETRIVED, pack('C', $this->_moveSpeed));
						break;
					case self::SET_SPEED:
						$inputFormated = unpack('Cspeed', $input);
						$response = $this->_setSpeed($inputFormated['speed']);
						break;
					case self::HOLDING_LEFT:
						$this->_holdingLeft();
						$response = NULL;
						break;
					case self::HOLDING_RIGHT:
						$this->_holdingRight();
						$response = NULL;
						break;
					case self::HOLDING_FORWARD:
						$this->_holdingForward();
						$response = NULL;
						break;
					case self::HOLDING_BACK:
						$this->_holdingBack();
						$response = NULL;
						break;
					case self::STOP_HOLDING_LEFT:
						$this->_stopHoldingLeft();
						$response = NULL;
						break;
					case self::STOP_HOLDING_RIGHT:
						$this->_stopHoldingRight();
						$response = NULL;
						break;
					case self::STOP_HOLDING_FORWARD:
						$this->_stopHoldingForward();
						$response = NULL;
						break;
					case self::STOP_HOLDING_BACK:
						$this->_stopHoldingBack();
						$response = NULL;
						break;
						
					default:
						$response = new SpykeeResponse(self::STATE_ERROR, self::RECEIVE_UNKNOW_PACKET);
						break;
					}
					/*
					 * Responds to the host
					*/
					if ($response != NULL) // If we have to send a packet tot the host
						// Send the packet
						$this->_sendPacketToClient($this->_socketsClient[$i]['socket'], $responseType, $response->getState(), $response->getIdDescription(), $response->getData());
				}
			}
		}
		return TRUE;
	}
	
	/**
	 * Disconnects a client
	 * @param integer $clientId Client id of the $_socketsClient array
	 */
	protected function _closeClientSocket($clientId){
		socket_close($this->_socketsClient[$clientId]['socket']);
		$this->_errorManager->writeLog('The user '.$this->_socketsClient[$clientId]['ip'].':'.$this->_socketsClient[$clientId]['port'].' has been disconnected', 2);
		// Delete socket of list of socket
		foreach($this->_socketsClient as $id => $value){
			if ($value['socket'] === $this->_socketsClient[$clientId]['socket']){
				unset($this->_socketsClient[$id]);
				break;
			}
		}
	}
	
	/**
	 * Sends a packet to the host
	 * @param resource $socket
	 * @param integer $type
	 * @param integer $state
	 * @param integer $idDescription
	 * @param integer $dataLength
	 * @param string $data Data have to be formated with pack function for example
	 */
	protected function _sendPacketToClient($socket, $type, $state, $idDescription, $data=null){
		$dataLength = (!empty($data)) ? strlen($data) : 0;
		$message = pack('a3CCCn', 'CTR', $type, $state, $idDescription, $dataLength);
		if (!empty($data))
			$message .= $data;
		if(@socket_send($socket, $message, strlen($message), 0) === false){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->_errorManager->writeLog('Unable to send packet to host: ['.$errorCode.'] '.$errorMsg, 1);
		}
		else
			$this->_errorManager->writeLog('Packet sent: '.$this->_packetToString($message, self::RESPONSE_PACKET), 3);
	}
	
	/**
	 * Formats the display of packets
	 * @param string $packet
	 * @param integer $type Type of packet self::RESPONSE_PACKET or self::REQUEST_PACKET
	 * @return string
	 */
	protected function _packetToString($packet, $type){
		if ($type == self::RESPONSE_PACKET){
			$header = unpack('a3header/Ctype/Cstate/CidDescription/nlength', $packet);
			if ($header['length']>0)
				$data = substr($packet, self::CTR_PACKET_RESPONSE_HEADER_SIZE);
			else
				$data=null;
		}
		else if ($type == self::REQUEST_PACKET){
			$header = unpack('a3header/Ctype/nlength', $packet);
			if ($header['length']>0)
				$data = substr($packet, self::CTR_PACKET_REQUEST_HEADER_SIZE);
			else
				$data=null;
		}
		switch($header['type']){
			case self::MOVE:
				$type='Move';
				$dataFormated=unpack('Cleft/Cright', $data);
				$data='left:'.$dataFormated['left'].' right:'.$dataFormated['right'];
			break;
			case self::LEFT:
				$type='Left';
			break;
			case self::RIGHT:
				$type='Right';
			break;
			case self::FORWARD:
				$type='Forward';
			break;
			case self::BACK:
				$type='Back';
			break;
			case self::STOP:
				$type='Stop';
			break;
			case self::ACTIVATE:
				$type='Activate';
			break;
			case self::CHARGE_STOP:
				$type='Charge stop';
			break;
			case self::DOCK:
				$type='Dock';
			break;
			case self::DOCK_CANCEL:
				$type='Dock cancel';
			break;
			case self::WIRELESS_NETWORKS:
				$type='Wireless networks';
			break;
			case self::GET_LOG:
				$type='Get log';
			break;
			case self::GET_CONFIG:
				$type='Get config';
			break;
			case self::STOP_CONTROLLER:
				$type='Stop controller';
			break;
			case self::GET_POWER_LEVEL:
				$type='Get power level';
			break;
			case self::REFRESH_POWER_LEVEL:
				$type='Refresh power level';
			break;
			case self::CONNECTION_TO_CONTROLLER:
				$type='Connection to controller';
			break;
			case self::SEND_MP3:
				$type='Send mp3';
			break;
			case self::AUDIO_PLAY:
				$type='Audio play';
			break;
			case self::HOLDING_LEFT:
				$type='Holding left';
			break;
			case self::HOLDING_RIGHT:
				$type='Holding right';
			break;
			case self::HOLDING_FORWARD:
				$type='Holding forward';
			break;
			case self::HOLDING_BACK:
				$type='Holding back';
			break;
			case self::STOP_HOLDING_LEFT:
				$type='Stop holding left';
			break;
			case self::STOP_HOLDING_RIGHT:
				$type='Stop holding right';
			break;
			case self::STOP_HOLDING_FORWARD:
				$type='Stop holding forward';
			break;
			case self::STOP_HOLDING_BACK:
				$type='Stop holding back';
			break;
			case self::VIDEO:
				$data = unpack('Cvideo', $data);
				$data = $data['video'];
				$type='Video';
			break;
			case self::SET_SPEED:
				$type='Set speed';
				if (!empty($data)){ // If is a request packet
					$data = unpack('Cspeed', $data);
					$data = $data['speed'];
				}
			break;
			case self::GET_SPEED:
				$type='Get speed';
				if (!empty($data)){ // If is a request packet
					$data = unpack('Cspeed', $data);
					$data = $data['speed'];
				}
			break;
			
			default:
				$type='Unknow';
			break;
		}
		
		$result = 'Header:'.$header['header'];
		$result .= '/Type:'.$type;
		if (!empty($header['state']))
			$result .= '/State:'.$header['state'];
		if (!empty($header['idDescription']))
			$result .= '/idDescription:'.$header['idDescription'];
		$result .= '/Length:'.$header['length'];
		if (!empty($data))
			$result .= '/Data:['.$data.']';
		
		return $result;
	}
	
	/**
	 * Retrieves responses of robot like battery status or stream audio/video
	 * @return boolean
	 */
	protected function _listenRobotResponses(){
		$result = $this->_SpykeeRobotClient->socketHook();
		// If no packet has been captured
		if(empty($result))
			return FALSE;
			
		foreach($result as $packet){
			// If the function have returned an image of the video stream
			if ($packet->getIdDescription() == SpykeeResponse::RECEIVE_PACKET_TYPE_VIDEO){
				$file = $this->_config->stream->videoPath.'video.jpeg';
				if (file_put_contents($file, $packet->getData()) === false)
					$this->_errorManager->error('Unable to write the image of video stream. ('.$file.')', 1);
			}
			// If the function have returned the battery level
			elseif ($packet->getIdDescription() == SpykeeResponse::RECEIVE_PACKET_TYPE_POWER AND $packet->getState() == SpykeeRobot::STATE_OK){
				$this->_powerLevel = $packet->getData(); 
			}
		}
		return TRUE;
	}
	
	/**
	 * Sends the periodic packets
	 */
	protected function _sendPeriodicPaquets(){
		// Go only at left
		if ($this->_holdingQueue['left'] AND !$this->_holdingQueue['back'] AND !$this->_holdingQueue['forward'])
			$this->_SpykeeRobotClient->left();
		// Go only at right
		else if ($this->_holdingQueue['right'] AND !$this->_holdingQueue['back'] AND !$this->_holdingQueue['forward'])
			$this->_SpykeeRobotClient->right();
		
		// Go forward
		else if ($this->_holdingQueue['forward'] AND !$this->_holdingQueue['left'] AND !$this->_holdingQueue['right'])
			$this->_SpykeeRobotClient->forward();
		// Go back
		else if ($this->_holdingQueue['back'] AND !$this->_holdingQueue['left'] AND !$this->_holdingQueue['right'])
			$this->_SpykeeRobotClient->back();
		
		// Go to the top left
		else if ($this->_holdingQueue['forward'] AND $this->_holdingQueue['left'])
			$this->_SpykeeRobotClient->move((int) ($this->_moveSpeed/5), $this->_moveSpeed);
		// Go to the top right
		else if ($this->_holdingQueue['forward'] AND $this->_holdingQueue['right'])
			$this->_SpykeeRobotClient->move($this->_moveSpeed, (int) ($this->_moveSpeed/8));
		// Go to the down left
		else if ($this->_holdingQueue['back'] AND $this->_holdingQueue['left'])
			$this->_SpykeeRobotClient->move(128 + (int) ($this->_moveSpeed/5), 128 + $this->_moveSpeed);
		// Go to the down right
		else if ($this->_holdingQueue['back'] AND $this->_holdingQueue['right'])
			$this->_SpykeeRobotClient->move(128 + $this->_moveSpeed, 128 + (int) ($this->_moveSpeed/5));
	}
	
	/**
	 * Until the stop command is sent (or inverse command), turn left
	 */
	protected function _holdingLeft(){
		if ($this->_holdingQueue['right'])
			$this->_holdingQueue['right'] = false;
		if ($this->_holdingQueue['left'])
			$this->_stopHoldingLeft();
		else
			$this->_holdingQueue['left'] = true;
	}
	
	/**
	 * Until the stop command is sent (or inverse command), turn right
	 */
	protected function _holdingRight(){
		if ($this->_holdingQueue['left'])
			$this->_holdingQueue['left'] = false;
		if ($this->_holdingQueue['right'])
			$this->_stopHoldingRight();
		else
			$this->_holdingQueue['right'] = true;
	}
	
	/**
	 * Until the stop command is sent (or inverse command), go forward
	 */
	protected function _holdingForward(){
		if ($this->_holdingQueue['back'])
			$this->_holdingQueue['back'] = false;
		if ($this->_holdingQueue['forward'])
			$this->_stopHoldingForward();
		else
			$this->_holdingQueue['forward'] = true;
	}
	
	/**
	 * Until the stop command is sent (or inverse command), go back
	 */
	protected function _holdingBack(){
		if ($this->_holdingQueue['forward'])
			$this->_holdingQueue['forward'] = false;
		if ($this->_holdingQueue['back'])
			$this->_stopHoldingBack();
		else
			$this->_holdingQueue['back'] = true;
	}
	
	/**
	 * Stop the command "go left all the time"
	 */
	protected function _stopHoldingLeft(){
		$this->_holdingQueue['left'] = false;
		$this->_SpykeeRobotClient->stop();
	}
	
	/**
	 * Stop the command "go right all the time"
	 */
	protected function _stopHoldingRight(){
		$this->_holdingQueue['right'] = false;
		$this->_SpykeeRobotClient->stop();
	}
	
	/**
	 * Stop the command "go forward all the time"
	 */
	protected function _stopHoldingForward(){
		$this->_holdingQueue['forward'] = false;
		$this->_SpykeeRobotClient->stop();
	}
	
	/**
	 * Stop the command "go back all the time"
	 */
	protected function _stopHoldingBack(){
		$this->_holdingQueue['back'] = false;
		$this->_SpykeeRobotClient->stop();
	}
	
	/**
	 * Sets the robot speed
	 * 1 lowest
	 * 128 highest
	 * @param int $value
	 */
	protected function _setSpeed($value){
		if ($value < 0 AND $value > 128) $value = $this->_config->robot->defaultSpeed;
		$this->_moveSpeed = $value;
		return $this->_SpykeeRobotClient->setSpeed($value);
	}
}

?>
