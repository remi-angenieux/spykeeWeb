<?php
/**
 * Content all methodes used to get informations about an action
 * All actions returns an SpykeeResponse object
 * @author Remi ANGENIEUX
 */
class SpykeeResponse{
	
	const NO_DESCRIPTION = 0;
	
	const NUMBER_RECONNEXION_EXCEEDED = 1;
	const ERROR_SEND_PACKET = 2;
	const PACKET_SENT = 3;
	const ERROR_RECEIVE_PACKET = 4;
	const ERROR_INCORRECT_HEADER = 20;
	const CONNECTION_REINIT = 5;
	const UNABLE_READ_DATA = 6;
	const DISCONNECTED_FROM_CONTROLLER = 21;
	const RECEIVE_NON_VALID_PACKET = 22;
	const UNABLE_TO_CONNECT_TO_CONTROLLER = 23;
	
	const RECEIVE_PACKET_TYPE_AUDIO = 7;
	const RECEIVE_PACKET_TYPE_VIDEO = 8;
	const RECEIVE_PACKET_TYPE_POWER = 9;
	const RECEIVE_PACKET_TYPE_AUTH_REPLY = 10;
	const RECEIVE_PACKET_TYPE_STOP = 11;
	const RECEIVE_PACKET_TYPE_WIRELESS_NETWORKS = 12;
	const RECEIVE_PACKET_TYPE_CONFIG = 13;
	const RECEIVE_PACKET_TYPE_LOG = 14;
	const RECEIVE_UNKNOW_PACKET = 15;
	
	const LEVEL_BATTERY_RETRIVED = 16;
	const TOO_MANY_CONNECTION = 17;
	const MOVE_SPEED_RETRIVED = 18;
	const MOVE_SPEED_CHANGED = 19;
	
	protected $_state=NULL;
	protected $_data=NULL;
	protected $_idDescription=NULL;
	protected $_description=NULL;
	
	/**
	 * Create a new Spykee response
	 * @param integer $state SpykeeRobot::STATE_OK or SpykeRobot::STATE_ERROR
	 * @param integer $idDescription Id description content in SpykeeResponse object
	 * @param string $data Data, like battery level
	 */
	public function __construct($state, $idDescription=self::NO_DESCRIPTION, $data=''){
		$this->_state = $state;
		$this->_idDescription = $idDescription;
		$this->_data = $data;
	}
	
	/**
	 * To get state of this response
	 * @return integer SpykeeRobot::STATE_OK or SpykeRobot::STATE_ERROR
	 */
	public function getState(){
		return $this->_state;
	}
	
	/**
	 * To get id description of this response
	 * @return integer Id description content in SpykeeResponse object
	 */
	public function getIdDescription(){
		return $this->_idDescription;
	}
	
	/**
	 * To get description message of this response
	 * @return string Description message
	 */
	public function getDescription(){
		// If the text of the description has already been retrieved once
		if (!empty($this->_description))
			return $this->_description;
		// If no description has been defined. Normally it's not possible, a default id was setted
		if (empty($this->_idDescription)){
			$this->_description = 'No id description has been defined. So there is no description';
			return $this->_description;
		}
		// Else we look for description message
		switch($this->_idDescription){
			case self::NUMBER_RECONNEXION_EXCEEDED:
				$this->_description = 'Number of reconnection attempts exceeded';
				break;
			case self::ERROR_SEND_PACKET:
				$this->_description = 'Unable to send the packet';
				break;
			case self::PACKET_SENT:
				$this->_description = 'The packet has been sent';
				break;
			case self::ERROR_RECEIVE_PACKET:
				$this->_description = 'Problem receiving the packet';
				break;
			case self::ERROR_INCORRECT_HEADER:
				$this->_description = 'Received bad header';
				break;
			case self::CONNECTION_REINIT:
				$this->_description = 'Connection reset';
				break;
			case self::UNABLE_READ_DATA:
				$this->_description = 'Unable to read the data carried in the packet';
				break;
			case self::DISCONNECTED_FROM_CONTROLLER:
				$this->_description = 'Controller close the connection';
				break;
			case self::RECEIVE_NON_VALID_PACKET:
				$this->_description = 'Received an invalid packet. The header is not good';
				break;
			case self::UNABLE_TO_CONNECT_TO_CONTROLLER:
				$this->_description = 'Unable to connect to the controller';
				break;
				
			case self::RECEIVE_PACKET_TYPE_AUDIO:
				$this->_description = 'Received packet: type audio';
				break;
			case self::RECEIVE_PACKET_TYPE_VIDEO:
				$this->_description = 'Packet received: video type';
				break;
			case self::RECEIVE_PACKET_TYPE_POWER:
				$this->_description = 'Packet received: battery level';
				break;
			case self::RECEIVE_PACKET_TYPE_AUTH_REPLY:
				$this->_description = 'Packet received: auth reply';
				break;
			case self::RECEIVE_PACKET_TYPE_STOP:
				$this->_description = 'Packet received: stop';
				break;
			case self::RECEIVE_PACKET_TYPE_WIRELESS_NETWORKS:
				$this->_description = 'Packet received: type wireless networks';
				break;
			case self::RECEIVE_PACKET_TYPE_CONFIG:
				$this->_description = 'Packet received: type config';
				break;
			case self::RECEIVE_PACKET_TYPE_LOG:
				$this->_description = 'Packet received: type log';
				break;
				$this->_description = 'Paquet reçu de type inconnu';
				break;
				
			case self::LEVEL_BATTERY_RETRIVED:
				$this->_description = 'Battery Level retrieved';
				break;
			case self::MOVE_SPEED_RETRIVED:
				$this->_description = 'Retrieved speed';
				break;
			case self::MOVE_SPEED_CHANGED:
				$this->_description = 'Speed ​​successfully changed';
				break;
			case self::TOO_MANY_CONNECTION:
				$this->_description = 'The maximum number of simultaneous connections has been reached';
				break;
				
			case self::NO_DESCRIPTION:
			default:
				$this->_description = 'No description has been defined';
				break;
				
			return $this->_description;
		}
		
		return $this->_description;
	}
	
	/**
	 * To get data of this response
	 * @return string
	 */
	public function getData(){
		return $this->_data;
	}
	
	/**
	 * To export this object in Json format
	 * @return string Object exported in Json format
	 */
	public function jsonFormat(){
		$return = '{';
		$return .= '"state": '.$this->getState().',';
		$return .= '"data": "'.$this->getData().'",';
		$return .= '"description": "'.$this->getDescription().'",';
		$return .= '"idDescription": "'.$this->getIdDescription().'"';
		$return .= '}';
		return $return;
	}
}


?>