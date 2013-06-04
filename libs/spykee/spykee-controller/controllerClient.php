<?php
/**
 * Content all methodes to control a robot through a controller
 * @author Remi ANGENIEUX
 */
// Includes shared constants
require_once(PATH.'libs/spykee/spykee-controller/controller.php');
// Includes the object used in the returns of the different robot actions
require_once(PATH.'libs/spykee/response.php');
// Include error manager
require_once(PATH.'libs/spykee/error.php');
class SpykeeControllerClient extends SpykeeController{
	protected $_controllerPort;
	protected $_controllerIp;
	protected $_socket;
	protected $_connectionTimeout;
	protected $_timeouts=array();
	
	// Defaults timeout for connection, read, write (socket)
	const DEFAULT_CONNECTION_TIMEOUT_SEC=1;
	const DEFAULT_CONNECTION_TIMEOUT_USEC=0;
	const DEFAULT_READ_TIMEOUT_SEC=1;
	const DEFAULT_READ_TIMEOUT_USEC=0;
	const DEFAULT_WRITE_TIMEOUT_SEC=1;
	const DEFAULT_WRITE_TIMEOUT_USEC=0;

	/**
	 * Create a new connection to a controller
	 * @param string $controllerIp IP addresse of the controller
	 * @param integer $controllerPort Port addresse of the controller
	 * @param array $timeouts Array of timeout (connection/read/write)
	 */
	function __construct($controllerIp, $controllerPort, $timeouts=NULL){
		$this->_setControllerIp($controllerIp);
		$this->_setControllerPort($controllerPort);
		$this->_setTimeouts($timeouts);
		$this->_connectToTheController();
	}
	
	/**
	 * Verify if the input value is an IP addresse otherwise generate an error
	 * @param string $ip
	 * @throws SpykeeException
	 */
	protected function _setControllerIp($ip){
		if (filter_var($ip, FILTER_VALIDATE_IP)) // If the use enter a valid IP addresse
			$this->_controllerIp = $ip;
		else{
			// Send error with 2 methodes because it's critical programming error
			// And kill the script
			$trace = debug_backtrace();
			$errorMessage = 'Argument 2 for SpykeeControllerClient::__construct() have to be an valid IP addresse, called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			throw new SpykeeException('Unable to launch Spykee Script', $errorMessage);
		}
	}
	
	/**
	 * Detect if the input value is port addresse otherwise generate an error
	 * @param mixed $port
	 * @throws SpykeeException
	 */
	protected function _setControllerPort($port){
		if (is_numeric($port) AND $port > 0 AND $port <= 49151)
			$this->_controllerPort = $port;
		else{
			// Send error with 2 methodes because it's critical programming error
			// And kill the script
			$trace = debug_backtrace();
			$errorMessage = 'Argument 3 for SpykeeControllerClient::__construct() have to be an valid port addresse (between 1 and 49151 included), called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			throw new SpykeeException('Unable to launch Spykee Script', $errorMessage);
		}
	}
	
	/**
	 * Detect if the input value is a correct array of timeout otherwise generate an error
	 * @param array $array
	 * @throws ExeceptionSpykee
	 * @return boolean
	 */
	protected function _setTimeouts($array){
		// Defaults timeout
		$this->_timeouts=array('connection' => array('sec' => self::DEFAULT_CONNECTION_TIMEOUT_SEC, 'usec' => self::DEFAULT_CONNECTION_TIMEOUT_USEC),
				'read' => array('sec' => self::DEFAULT_READ_TIMEOUT_SEC, 'usec' => self::DEFAULT_READ_TIMEOUT_USEC),
				'write' => array('sec' => self::DEFAULT_WRITE_TIMEOUT_SEC, 'usec' => self::DEFAULT_WRITE_TIMEOUT_USEC));
		// Detect errors
		if (empty($array)){
			return TRUE; // We have nothing to do, so we quit
		}
		elseif (!is_array($array)){
			$trace = debug_backtrace();
			$errorMessage = 'Argument 4 for SpykeeControllerClient::__construct() have to be an array, called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			throw new ExeceptionSpykee('Unable to launch Spykee Script', $errorMessage);
		}
		
		// Set timeouts
		if(!empty($array['connection'])){
			if ($this->_timeouts['connection']['usec'] >= 1000){
				$trace = debug_backtrace();
				$errorMessage = 'Argument 4 for SpykeeControllerClient::__construct() usec have to be less than 1000 else add 1 to the sec, called in'
						.$trace[0]['file'].' on line '.$trace[0]['line'];
				throw new ExeceptionSpykee('Unable to launch Spykee Script', $errorMessage);
			}
			$this->_timeouts['connection']['sec'] = (!empty($array['connection']['sec'])) ? $array['connection']['sec'] : 0;
			$this->_timeouts['connection']['sec'] = (!empty($array['connection']['usec'])) ? $array['connection']['usec'] : 0;
		}
		if (!empty($array['read'])){
			if ($this->_timeouts['read']['usec'] >= 1000){
				$trace = debug_backtrace();
				$errorMessage = 'Argument 4 for SpykeeControllerClient::__construct() usec have to be less than 1000 else add 1 to the sec, called in'
						.$trace[0]['file'].' on line '.$trace[0]['line'];
				throw new ExeceptionSpykee('Unable to launch Spykee Script', $errorMessage);
			}
			$this->_timeouts['read']['sec'] = (!empty($array['read']['sec'])) ? $array['read']['sec'] : 0;
			$this->_timeouts['read']['sec'] = (!empty($array['read']['usec'])) ? $array['read']['usec'] : 0;
		}
		if (!empty($array['write'])){
			if ($this->_timeouts['write']['usec'] >= 1000){
				$trace = debug_backtrace();
				$errorMessage = 'Argument 4 for SpykeeControllerClient::__construct() usec have to be less than 1000 else add 1 to the sec, called in'
						.$trace[0]['file'].' on line '.$trace[0]['line'];
				throw new ExeceptionSpykee('Unable to launch Spykee Script', $errorMessage);
			}
			$this->_timeouts['write']['sec'] = (!empty($array['write']['sec'])) ? $array['write']['sec'] : 0;
			$this->_timeouts['write']['sec'] = (!empty($array['write']['usec'])) ? $array['write']['usec'] : 0;
		}
		return TRUE;
	}

	/**
	 * Connect to the controller
	 * @throws SpykeeException
	 */
	protected function _connectToTheController(){
		if(!($this->_socket = socket_create(AF_INET, SOCK_STREAM, 0))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
		
			throw new SpykeeException('Launching error', 'Could not create socket: ['.$errorCode.'] '.$errorMsg, $errorCode);
		}
		// Defines a timeout for receiving packet
		if(!@socket_set_option($this->_socket, SOL_SOCKET, SO_SNDTIMEO, array('sec'=> $this->_timeouts['connection']['sec'], 'usec'=> $this->_timeouts['connection']['usec']))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			throw new SpykeeException('Launching error', 'Unable to define timeout for controller socket: ['.$errorCode.'] '.$errorMsg, $errorCode);
		}
		if(!@socket_connect($this->_socket , $this->_controllerIp, $this->_controllerPort)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			throw new SpykeeException('Launching error', 'Unable to connect to the controller: ['.$errorCode.'] '.$errorMsg, $errorCode);
		}
		// Set timeouts for reading
		if(!@socket_set_option($this->_socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->_timeouts['read']['sec'], 'usec' => $this->_timeouts['read']['usec']))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			throw new SpykeeException('Launching error', 'Unable to set read timeout of controller socket: ['.$errorCode.'] '.$errorMsg, $errorCode);
		}
		// Set timeouts for writing
		if(!@socket_set_option($this->_socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $this->_timeouts['write']['sec'], 'usec' => $this->_timeouts['write']['usec']))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			throw new SpykeeException('Launching error', 'Unable to set write timeout of controller socket: ['.$errorCode.'] '.$errorMsg, $errorCode);
		}
	}
	
	/**
	 * Send actions to the controller
	 * @param integer $type Type content in SpykeeController object
	 * @param string $data Data have to be formated with pack function for example
	 * @return SpykeeResponse
	 */
	protected function _sendPacketToController($type, $data=NULL){
		$length = (!empty($data)) ? strlen($data) : 0;
		$msg = pack('a3Cn', 'CTR', $type, $length);
		if (!empty($data))
			$msg .= $data;
		if (!@socket_send($this->_socket, $msg, strlen($msg))){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			// FIXME: A supprimer
			echo $errorMsg;
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_SEND_PACKET);
		}
		return new SpykeeResponse(self::STATE_OK, SpykeeResponse::PACKET_SENT);
	}

	/**
	 * Receives the packet and processes
	 * @return SpykeeResponse
	 */
	protected function _getResponse(){
		socket_clear_error();
		$response = @socket_read($this->_socket, self::CTR_PACKET_HEADER_SIZE);
		// If an error occured
		if (($errorCode = socket_last_error()) != 0){
			// If we can't read data, the connection is corrupted. So we disconnect to the controller
			$this->_closeSocket();
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_RECEIVE_PACKET);
		}
		// If the client want to disconnect
		if ($response == '' OR $response === false){
			$this->_closeSocket();
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::DISCONNECTED_FROM_CONTROLLER);
		}
		$header = unpack('a3header/Ctype/Cstate/CidDescription/nlength', $response);
		// If header isn't CTR, the packet isn't send by valid controller
		if ($header['header'] != 'CTR'){
			$this->_closeSocket();
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::RECEIVE_NON_VALID_PACKET);
		}
		if (!empty($header['length']) AND $header['length'] > 0){
			if(socket_recv($this->_socket, $data, $header['length'], MSG_WAITALL ) === FALSE){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
				return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::UNABLE_READ_DATA);
			}
			$data = unpack('Cdata', $data);
			$data = $data['data'];
		}
		else
			$data=null;
		return new SpykeeResponse($header['state'], $header['idDescription'], $data);
	}


	/**
	 * To close connection
	 */
	protected function _closeSocket(){
		@socket_close($this->_socket);
		@fclose($this->_stream);
	}
	
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
	public function move($left, $right){
		$left = ($left < 256) ? $left : 255;
		$left = ($left >= 0) ? $left : 0;
		$right = ($right < 256) ? $right : 255;
		$right = ($right >= 0) ? $right : 0;
		return $this->__sendPacketToController(self::MOVE, pack('CC', $left, $right));
	}

	/**
	 * Turn left
	 * @return SpykeeResponse
	 */
	public function left(){
		return $this->_sendPacketToController(self::LEFT);
	}

	/**
	 * Turn right
	 * @return SpykeeResponse
	 */
	public function right(){
		return $this->_sendPacketToController(self::RIGHT);
	}

	/**
	 * Forward
	 * @return SpykeeResponse
	 */
	public function forward(){
		return $this->_sendPacketToController(self::FORWARD);
	}

	/**
	 * Back
	 * @return SpykeeResponse
	 */
	public function back(){
		return $this->_sendPacketToController(self::BACK);
	}

	/**
	 * Stop wheels
	 * @return SpykeeResponse
	 */
	public function stop(){
		return $this->_sendPacketToController(self::STOP);
	}
	
	/**
	 * Until the stop command is sent (or inverse command), turn left
	 * @return SpykeeResponse
	 */
	public function holdingLeft(){
		return $this->_sendPacketToController(self::HOLDING_LEFT);
	}
	
	/**
	 * Until the stop command is sent (or inverse command), turn right
	 * @return SpykeeResponse
	 */
	public function holdingRight(){
		return $this->_sendPacketToController(self::HOLDING_RIGHT);
	}
	
	/**
	 * Until the stop command is sent (or inverse command), go forward
	 * @return SpykeeResponse
	 */
	public function holdingForward(){
		return $this->_sendPacketToController(self::HOLDING_FORWARD);
	}
	
	/**
	 * Until the stop command is sent (or inverse command), go back
	 * @return SpykeeResponse
	 */
	public function holdingBack(){
		return $this->_sendPacketToController(self::HOLDING_BACK);
	}

	/**
	 * Forward slightly to undock the robot
	 * @return SpykeeResponse
	 */
	public function activate(){
		return $this->_sendPacketToController(self::ACTIVATE);
	}

	/**
	 * Stop charging the robot
	 * @return SpykeeResponse
	 */
	public function chargeStop(){
		return $this->_sendPacketToController(self::CHARGE_STOP);
	}

	/**
	 * Order the robot to go to the charging station. It can fail
	 * @return SpykeeResponse
	 */
	public function dock(){
		return $this->_sendPacketToController(self::DOCK);
	}

	/**
	 * Stop the order to go to the charging station
	 * @return SpykeeResponse
	 */
	public function dockCancel(){
		return $this->_sendPacketToController(self::DOCK_CANCEL);
	}

	/*public function sendMp3(){
		return $this->_sendPacketToController(self::SEND_MP3);
	}
	
	public function audioPlay(){
		return $this->_sendPacketToController(self::AUDIO_PLAY);
	}*/
	/**
	 * Retrieves the list of wireless access points within range of the robot
	 * @return SpykeeResponse
	 */
	public function wirelessNetworks(){
		$request = $this->_sendPacketToController(self::WIRELESS_NETWORKS);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}
	
	/**
	 * Get Spykee log (Log IN the robot)
	 * @return SpykeeResponse
	 */
	public function getLog(){
		$request = $this->_sendPacketToController(self::GET_LOG);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}

	/**
	 * Get spykee config (Config IN the robot)
	 * @return SpykeeResponse
	 */
	public function getConfig(){
		$request = $this->_sendPacketToController(self::GET_CONFIG);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse(); // TODO mettre en forme la sortie
		else
			return $request;
	}
	
	/**
	 * Get stored battery level
	 * Level battery is often updated
	 * @return SpykeeResponse
	 */
	public function getPowerLevel(){
		$request = $this->_sendPacketToController(self::GET_POWER_LEVEL);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse();
		else
			return $request;
	}
	
	/**
	 * Get current battery level
	 * @return SpykeeResponse
	 */
	public function refreshPowerLevel(){
		$request = $this->_sendPacketToController(self::REFRESH_POWER_LEVEL);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse();
		else
			return $request;
	}
	
	/**
	 * Enables or disables video stream
	 * @param bool $bool TRUE: enabled FALSE: disbaled
	 * @return SpykeeResponse
	 */
	public function setVideo($bool){
		$status = ($bool == true) ? 1 : 0;
		//return $this->setVideo($status);
		return $this->_sendPacketToController(self::VIDEO, pack('C', $status));
	}
	
	/**
	 * Get the robot speed
	 * @return SpykeeResponse
	 */
	public function getSpeed(){
		$request = $this->_sendPacketToController(self::GET_SPEED);
		if ($request->getState() == self::STATE_OK) // If the packet is send sucefully
			return $this->_getResponse();
		else
			return $request;
	}
	
	/**
	 * Sets the robot speed
	 * 1 lowest
	 * 128 highest
	 * @param integer $value
	 * @return SpykeeResponse
	 */
	public function setSpeed(int $value){
		if ($value < 1) $value=1;
		if ($value > 128) $value=128;
		return $this->_sendPacketToController(self::SET_SPEED, pack('C', $value));
	}

	/**
	 * Stop controller server
	 * @return SpykeeResponse
	 */
	public function stopController(){
		return $this->_sendPacketToController(self::STOP_CONTROLLER);
	}

	function __destruct(){
		$this->_closeSocket();
	}


}


?>