<?php
/**
 * Content all methodes used to control easily the robot
 * @author Remi ANGENIEUX
 */
// Includes shared constants
require_once(PATH.'/libs/spykee/spykee-robot/robot.php');
// Include the response object
require_once(PATH.'libs/spykee/response.php');
// Include config manager
require_once(PATH.'libs/spykee/config.php');
// Include error manager
require_once(PATH.'libs/spykee/error.php');
class SpykeeRobotClient extends SpykeeRobot {
	/*
	 * Spykee protocol
	 */
	const PACKET_HEADER_SIZE = 5;
	const PACKET_DATA_SIZE_MAX = 32768; //32*1024
	// Type of paquets
	const PACKET_TYPE_AUDIO = 1;
	const PACKET_TYPE_VIDEO = 2;
	const PACKET_TYPE_POWER = 3;
	const PACKET_TYPE_MOVE = 5;
	const PACKET_TYPE_FILE = 6;
	const PACKET_TYPE_PLAY = 7;
	const PACKET_TYPE_STOP = 8;
	const PACKET_TYPE_AUTH_REQUEST = 10;
	const PACKET_TYPE_AUTH_REPLY = 11;
	const PACKET_TYPE_CONFIG  = 13;
	const PACKET_TYPE_WIRELESS_NETWORKS = 14;
	const PACKET_TYPE_STREAMCTL = 15;
	const PACKET_TYPE_ENGINE = 16;
	const PACKET_TYPE_LOG = 17;
	
	const FILE_ID_MUSIC= 64;
	const FILE_ID_FIRMWARE= 66;
	
	const SENDFILE_FLAG_NONE = 0;
	const SENDFILE_FLAG_BEGIN = 1;
	const SENDFILE_FLAG_END = 2;
	
	const MESSAGE_TYPE_ACTIVATE = 1;
	const MESSAGE_TYPE_CHARGE_STOP = 5;
	const MESSAGE_TYPE_BASE_FIND = 6;
	const MESSAGE_TYPE_BASE_FIND_CANCEL = 7;
	
	const STREAM_ID_VIDEO = 1;
	
	const ROBOT_PORT = 9000;
	
	protected $_config;
	protected $_robotName;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_robotIp;
	protected $_errorManager;
	protected $_robotSocket=NULL;
	protected $_robotStream=NULL;
	protected $_moveSpeed;
	protected $_powerLevel = NULL;
	protected $_reconnection=0;

	/**
	 * Create a new connection to the robot
	 * @param string $robotName
	 * @param string $robotIp
	 * @param string $robotUsername
	 * @param string $robotPassword
	 */
	public function __construct($robotName, $robotIp, $robotUsername=null, $robotPassword=null){
		$this->_robotName = $robotName;
		$this->_setRobotIp($robotIp); // Control the input
		try{
			$this->_config = new SpykeeConfig('spykeeRobot.ini');
		}
		catch (ExceptionSpykee $e){ // If an error ocurred at the init of the config object
			SpykeeError::standaloneError($e->getMessage());
			throw new ExceptionSpykee($e->getUserMessage(), $e->getMessage());
		}
		$this->_errorManager = new SpykeeError($this->_robotName, $this->_robotIp, $this->_config);
		$this->_reconnection=0;
		$this->_robotUsername = (!empty($robotUsername)) ? $robotUsername : $this->_config->robot->defaultUsername;
		$this->_robotPassword = (!empty($robotPassword)) ? $robotPassword : $this->_config->robot->defaultPassword;
		$this->_moveSpeed = $this->_config->robot->speed;
		
		$this->_initSocket();
		$this->_authentificationRobot();
	}
	
	/**
	 * Verify if the input value is an IP adresse otherwise generate an error
	 * @param string $ip
	 */
	protected function _setRobotIp($ip){
		if (filter_var($ip, FILTER_VALIDATE_IP)) // If the use enter a valid IP adresse
			$this->_robotIp = $ip;
		else{
			// Send error with 2 methodes because it's critical programming error
			// And kill the script
			$trace = debug_backtrace();
			$errorMessage = 'Argument 2 for SpykeeRobotClient::__construct() have to be an valid IP adresse, called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			SpykeeError::standaloneError($errorMessage);
			throw new ExceptionSpykee('Unable to launch Spykee Script', $errorMessage);
		}
	}
	
	/**
	 * Create the socket used for the connection
	 */
	protected function _initSocket(){
		$this->_robotStream = @fsockopen('tcp://'.$this->_robotIp, self::ROBOT_PORT, $errorCode, $errorMsg, $this->_config->robot->connectionTimeout);
		
		if ($this->_robotStream === FALSE){
			$this->_errorManager->error('Unable to connect to the robot: ['.$errorCode.'] '.$errorMsg, 1);
			throw new ExceptionSpykee('Connection error', 'Unable to connect to the robot');
		}
		$this->_robotSocket = socket_import_stream($this->_robotStream);
	}
	
	/**
	 * Close the socket connection
	 */
	protected function _closeSocket(){
		@socket_close($this->_robotSocket);
		@fclose($this->_robotStream);
	}

	/**
	 * Authenticates to the robot
	 */
	protected function _authentificationRobot(){
		// Send the auth request
		$this->_sendPacketToRobot(self::PACKET_TYPE_AUTH_REQUEST, $this->_packString($this->_robotUsername).$this->_packString($this->_robotPassword));
		if (false /* Connection failed*/) // TODO détection une mauvaise connexion
			throw new ExceptionSpykee('Connection error', 'Wrong Spykee login or password');
		$this->_getResponse(); // Wait the firmware version
		$this->_getResponse(); // Wait the battery level
		// Note : If you don't wait, and send move request before receive anything.
		// The robot close the connection
	}

	/**
	 * Format string to be send with Spykee protocol
	 * @param string $str
	 * @return string
	 */
	protected function _packString($str){
		return pack('Ca*', strlen($str), $str);
	}


	/**
	 * Send actions to the robot
	 * @param integer $type
	 * @param string $data
	 * @return SpykeeResponse
	 */
	protected function _sendPacketToRobot($type, $data=NULL){
		$strLen=(!empty($data)) ? strlen($data) : 0;
		$msg = pack('a2Cn', 'PK', $type, $strLen); // Paquet header
		if ($strLen>0)
			$msg .= $data; // If there is data to send is added to the header
		if(!socket_send($this->_robotSocket, $msg, strlen($msg), MSG_DONTROUTE)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			
			if ($errorCode == 32 ){ // Broken pipe. So reconnection
				// TODO étudier ce genre d'erreur
				$this->_reconnection++;
				$this->_errorManager->error('Broken pipe. Reconnection', 1);
				// If the number of reconnection is exceeded
				if ($this->_reconnection >= $this->_config->robot->nbReconnection){
					$this->_errorManager->error('Unable to reconnect to the robot after '.$this->_reconnection.' attempts.', 1);
					return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::NUMBER_RECONNEXION_EXCEEDED);
				}
				$this->_initSocket();
				$this->_authentificationRobot();
				$return = $this->_sendPacketToRobot($type, $data);
				/*if ($return->getState() == self::STATE_OK) // Reinit du compteur
					$this->_reconnection=0;*/
				return $return;
			}
			else{
				$this->_errorManager->error('Unable to send a paquet: "'.$this->_paquetToString($msg).'". ['.$errorCode.'] '.$errorMsg, 1);
				return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_SEND_PACKET);
			}
		}
		$this->_reconnection=0; // Reinit the counter
		$this->_errorManager->error('Paquet sent sucefully to the robot: '.$this->_paquetToString($msg), 3);
		return new SpykeeResponse(self::STATE_OK, SpykeeResponse::PACKET_SENT);
	}

	/**
	 * Receives the packet and processes
	 * @return SpykeeResponse
	 */
	protected function _getResponse(){
		if(socket_recv($this->_robotSocket, $response, self::PACKET_HEADER_SIZE, MSG_WAITALL ) === FALSE){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->_errorManager->error('Unable to receive paquet ['.$errorCode.'] '.$errorMsg, 1);
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_RECEIVE_PACKET);
		}
		
		// Reset of connection
		if (empty($response)){
			$this->_errorManager->error('Reset connection. Reconnexion', 1);
			$this->_initSocket();
			// TODO vérifier qu'une nouvelle authentification n'est pas requise
			//$this->_authentificationRobot();
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::CONNECTION_REINIT);
		}
		
		// Read the header
		$header = unpack('a2header/Ctype/nlength', $response);
		$this->_errorManager->writeLog('Paquet received : '.$this->_paquetToString($response), 3);
		// If header isn't PK, the paquet isn't send by the robot
		if ($header['header']!='PK'){
			$this->_errorManager->error('Paquet receive without the correct header: '.$header['header'], 1);
			$this->_closeSocket();
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_INCORRECT_HEADER);
		}
		// If data was send, read it
		if (!empty($header['length']) AND $header['length']>0){
			if(socket_recv($this->_robotSocket, $data, $header['length'], MSG_WAITALL ) === FALSE){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
				$this->_errorManager->error('Unable to read data sended ['.$errorCode.'] '.$errorMsg, 1);
				return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::UNABLE_READ_DATA);
			}
			$data = unpack('Cdata');
			$data = $data['data'];
		}
		
		$this->_errorManager->writeLog('Data transported : '.$data, 3);
		
		/*
		 * Response management
		*/
		$state = self::STATE_OK; // Default state
		switch($header['type']){
			case self::PACKET_TYPE_AUDIO:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_AUDIO;
				break;
			case self::PACKET_TYPE_VIDEO:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_VIDEO;
				break;
			case self::PACKET_TYPE_POWER:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_POWER;
				$this->_powerLevel = $data;
				break;
			case self::PACKET_TYPE_AUTH_REPLY:
				// $data
				// 0001 = Require connection
				// 0003 = Already connected
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_AUTH_REPLY;
				// TODO détecter les erreurs de connexion. Si il y en a une l'envoyé via un exception
				break;
			case self::PACKET_TYPE_STOP:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_STOP;
				break;
			case self::PACKET_TYPE_WIRELESS_NETWORKS:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_WIRELESS_NETWORKS;
				break;
			case self::PACKET_TYPE_CONFIG:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_CONFIG;
				break;
			case self::PACKET_TYPE_LOG:
				$description = SpykeeResponse::RECEIVE_PACKET_TYPE_LOG;
				break;
				
			default:
				$this->_errorManager->error('Unknow paquet with type: '.$header['type'], 1);
				$state = self::STATE_ERROR;
				$description = SpykeeResponse::RECEIVE_UNKNOW_PACKET;
				break;
		}

		return new SpykeeResponse($state, $description, $data);
	}
	
	/**
	 * Formats the display of packets
	 * @param string $paquet
	 * @return string
	 */
	protected function _paquetToString($paquet){
		$header = unpack('a2header/Ctype/nlength', $paquet);
		
		switch($header['type']){
			case self::PACKET_TYPE_AUDIO:
				$type='Audio';
			break;
			case self::PACKET_TYPE_VIDEO:
				$type='Video';
			break;
			case self::PACKET_TYPE_POWER:
				$type='Power';
			break;
			case self::PACKET_TYPE_MOVE:
				$type='Move';
			break;
			case self::PACKET_TYPE_FILE:
				$type='File';
			break;
			case self::PACKET_TYPE_PLAY:
				$type='Play';
			break;
			case self::PACKET_TYPE_STOP:
				$type='Stop';
			break;
			case self::PACKET_TYPE_AUTH_REQUEST:
				$type='Auth request';
			break;
			case self::PACKET_TYPE_AUTH_REPLY:
				$type='Auth reply';
			break;
			case self::PACKET_TYPE_CONFIG:
				$type='Config';
			break;
			case self::PACKET_TYPE_WIRELESS_NETWORKS:
				$type='Wireless networks';
			break;
			case self::PACKET_TYPE_STREAMCTL:
				$type='Stream control';
			break;
			case self::PACKET_TYPE_ENGINE:
				$type='Engine';
			break;
			case self::PACKET_TYPE_LOG:
				$type='Log';
			break;
			
			default:
				$type='Unknow';
			break;
		}
		
		$result = 'Header:'.$header['header'];
		$result .= '/Type:'.$type;
		$result .= '/Length:'.$header['length'];
		if ($header['length']!=0){
			$data = unpack('Cdata');
			$result .= '/Data:'.$data['data'];
		}
		
		return $result;
	}
	
	/*
	 * Users methodes
	 */

	/**
	 * Set speed of each wheel
	 * 0: slowest forward
	 * 128: fastest forward
	 * 129: slowest back
	 * 255: fastest back
	 * @param integer $left
	 * @param integer $right
	 * @return SpykeeResponse
	 */
	public function move(int $left, int $right){
		if ($left > 255) $left = 255;
		if ($left < 0) $left = 0;
		if ($right > 255) $right = 255;
		if ($right < 0) $right = 0;
		return $this->_sendPacketToRobot(self::PACKET_TYPE_MOVE, pack('CC', $left, $right));
		// The robot do not response to these paquet, so we don't receive anything
	}

	/**
	 * Turn left
	 * @return SpykeeResponse
	 */
	public function left(){
		//return $this->move(150, 100);
		// TODO a tester
		return $this->move(128 + $this->_moveSpeed, $this->_moveSpeed);
	}

	/**
	 * Turn right
	 * @return SpykeeResponse
	 */
	public function right(){
		//return $this->move(100, 150);
		return $this->move($this->_moveSpeed, 128 + $this->_moveSpeed);
	}

	/**
	 * Forward
	 * @return SpykeeResponse
	 */
	public function forward(){
		return $this->move($this->_moveSpeed, $this->_moveSpeed);
	}

	/**
	 * Back
	 * @return SpykeeResponse
	 */
	public function back(){
		return $this->move(128 + $this->_moveSpeed, 128 + $this->_moveSpeed);
	}

	/**
	 * Stop wheels
	 * @return SpykeeResponse
	 */
	public function stop(){
		return $this->move(0,0);
	}
	
	/**
	 * Forward slightly to undock the robot
	 * @return SpykeeResponse
	 */
	public function activate(){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_ENGINE, pack('C', self::MESSAGE_TYPE_ACTIVATE));
		// TODO savoir ce que retourne cette demande
	}

	/**
	 * Stop charging the robot
	 * @return SpykeeResponse
	 */
	public function chargeStop(){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_ENGINE, pack('C', self::MESSAGE_TYPE_CHARGE_STOP));
		// TODO savoir ce que retourne cette demande
	}

	/**
	 * Order the robot to go to the charging station. It can fail
	 * @return SpykeeResponse
	 */
	public function dock(){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_ENGINE, pack('C', self::MESSAGE_TYPE_BASE_FIND));
		// TODO savoir ce que retourne cette demande
	}

	/**
	 * Stop the order to go to the charging station
	 * @return SpykeeResponse
	 */
	public function dockCancel(){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_ENGINE, pack('C', self::MESSAGE_TYPE_BASE_FIND_CANCEL));
		// TODO savoir ce que retourne cette demande
	}

	/*public function sendFile($fileName, $file_id){
		$flag =self::SENDFILE_FLAG_BEGIN;
		print "Sending file $fileName\n";
		$fh=fopen($fileName,'r');
		$maxlen = self::PACKET_DATA_SIZE_MAX - self::PACKET_HEADER_SIZE;
		while ($contentlen = fread($fh, $content, $maxlen)) {
			if ($maxlen !=  $contentlen) {
				# End of file, set the end flag
				$flag | self::SENDFILE_FLAG_END;
			}
			$this->_sendPacketToRobot(self::PACKET_TYPE_FILE ,pack("CCA*", $file_id, $flag, $content));
			if ($flag & self::SENDFILE_FLAG_BEGIN) {
				print "<";
			} 
			if ($flag & self::SENDFILE_FLAG_END) {
				print ">";
			} 
			else {
				print ".";
			}
				# Clear begin flag
				$flag &= ~ self::SENDFILE_FLAG_BEGIN;
		}
		fclose($fh);
			print "\n";
	}
		
	public function audioPlay($idFile){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_PLAY, pack('C', $idFile));

	}

	public function audioStop(){
		return $this->_sendPacketToRobot(self::PACKET_TYPE_STOP);
	}
	
	public function sendMp3($fileName){
		return $this->send_file($fileName, self::FILE_ID_MUSIC);
	}*/

	/**
	 * Retrieves the list of wireless access points within range of the robot
	 * @return SpykeeResponse
	 */
	public function wirelessNetworks(){
		$request = $this->_sendPacketToRobot(self::PACKET_TYPE_WIRELESS_NETWORKS);
		if ($request->getState() == self::STATE_OK) // If the paquet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}

	/**
	 * Get Spykee log (Log IN the robot)
	 * @return SpykeeResponse
	 */
	public function getLog(){
		$request = $this->_sendPacketToRobot(self::PACKET_TYPE_LOG);
		if ($request->getState() == self::STATE_OK) // If the paquet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}

	/**
	 * Get spykee config (Config IN the robot)
	 * @return SpykeeResponse
	 */
	public function getConfig(){
		$request = $this->_sendPacketToRobot(self::PACKET_TYPE_CONFIG);
		if ($request->getState() == self::STATE_OK) // If the paquet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}

	/**
	 * Get stored battery level
	 * Without use socketHook. This method can return an old value, not updating
	 * @return SpykeeResponse
	 */
	public function getPowerLevel(){
		if ($this->_powerLevel != NULL) // If the battery level is already know
			return new SpykeeResponse(self::STATE_OK, SpykeeResponse::LEVEL_BATTERY_RETRIVED, $this->_powerLevel);
		else
			return $this->refresh_power_level();
	}

	/**
	 * Get current battery level
	 * @return SpykeeResponse
	 */
	public function refreshPowerLevel(){
		$request = $this->_sendPacketToRobot(self::PACKET_TYPE_POWER);
		if ($request->getState() == self::STATE_OK){ // If the paquet is send sucefully
			$response = $this->_getResponse();
			$this->_powerLevel = $response->getData();
			return $response;
		}
		else
			return $request;
	}
	
	/**
	 * Enables or disables the video stream
	 * @param bool $bool
	 * @return SpykeeResponse
	 */
	public function setVideo(bool $bool){
		$status = ($bool == true) ? 1 : 0;
		return $this->_sendPacketToRobot(self::PACKET_TYPE_STREAMCTL, pack('CC', self::STREAM_ID_VIDEO, $status));
	}
	
	/**
	 * Get the robot speed
	 * @return SpykeeResponse
	 */
	public function getSpeed(){
		return new SpykeeResponse(self::STATE_OK, SpykeeResponse::MOVE_SPEED_RETRIVED, $this->_moveSpeed);
	}

	/**
	 * Sets the robot speed
	 * @param integer $value
	 * @return SpykeeResponse
	 */
	public function setSpeed(int $value){
		if ($value < 0 AND $value > 128) $value = self::MOVE_SPEED;
		$this->_moveSpeed = $value;
		return new SpykeeResponse(self::STATE_OK, SpykeeResponse::MOVE_SPEED_CHANGED);
	}
	
	/**
	 * Useful method to retrieve packets sent by the robot independently of a request
	 * Used to retrieve the video stream, audio stream and battery status
	 * @return SpykeeResponse
	 */
	public function socketHook(){
		$write=NULL;
		$except=NULL;
		$read = array();
		$read[0]=$this->_robotStream;
		if(($test = stream_select($read, $write, $except, 0, NULL)) === false){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			$this->_errorManager->error('Unable to listen robot socket : ['.$errorCode.'] '.$errorMsg, 1);
			die;
		}
		foreach($read as $streamInput){
			if (is_resource($streamInput) AND $streamInput != '' AND $streamInput == $this->_robotStream){
				if (is_resource($streamInput) AND $streamInput != '')
					return $this->_getResponse();
			}
		}
	}
	
	public function __destruct(){
		// Close connection because PHP can't manage persistent connection
		$this->_closeSocket();
	}
}

?>