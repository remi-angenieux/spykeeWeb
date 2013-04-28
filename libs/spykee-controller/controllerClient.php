<?php
// Inclue la configuration du ControllerClient. Et ses constantes partagées
require_once(PATH.'configs/spykeeControllerClient.php');
// Inclue l'objet utilisé lors des retours des différentes actions
require_once(PATH.'libs/spykee-robot/robotResponse.php');

class SpykeeControllerClient extends SpykeeConfigRobot{

	/*
	 * Attributs
	*/
	protected $_serverPort;
	protected $_robotName;
	protected $_serverIp;
	protected $_socket;
	protected $_stream;
	

	function __construct($robotName, $serverIp, $serverPort){
		$this->_serverPort = $serverPort;
		$this->_robotName = $robotName;
		$this->_serverIp = $serverIp;
		$this->connectToTheController();
	}


	protected function connectToTheController(){
		$this->_stream = fsockopen('tcp://'.$this->_serverIp, $this->_serverPort, $errorCode, $errorMsg, self::CONNECTION_CONTROLLER_TIMEOUT);
		if ($this->_stream === FALSE){
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Couldn't create socket: [$errorCode] $errorMsg \n");
		}
		echo "Connection established \n";
		$this->_socket = socket_import_stream($this->_stream);
	}


	protected function sendPacketToController($type, $data=''){
		$length = (!empty($data)) ? strlen($data) : 0;
		// On ajoute l'état et la description bien qu'on en a pas l'interet pour garder le même format
		$msg = pack('a3CCCn', 'CTR', $type, self::STATE_OK, SpykeeResponse::NO_DESCRIPTION, $length);
		if (!empty($data))
			$msg .= $data;
		if (!socket_send($this->_socket, $msg, strlen($msg), MSG_DONTROUTE)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_SEND_PAQUET);
		}
		
		echo "Message send successfully \n";
		return new SpykeeResponse(self::STATE_OK, SpykeeResponse::PAQUET_SENT);
	}

	protected function getResponse(){
		// Récupération des données envoyées
		if(socket_recv($this->_socket, $response, self::CTR_PAQUET_HEADER_SIZE, MSG_WAITALL ) === FALSE)
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			
			return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::ERROR_RECEIVE_PAQUET);
		}
		
		$response = bin2hex($response);
		echo 'Reponse : '.$response;
		$header = hex2bin($response[0].$response[1].$response[2].$response[3].$response[4].$response[5]);
		$type = base_convert($response[6].$response[7], 16, 10);
		$state = base_convert($response[8].$response[9], 16, 10);
		$idDescription = base_convert($response[10].$response[11], 16, 10);
		$length = base_convert($response[12].$response[13].$response[14].$response[15], 16, 10);
		echo ' Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n";

		if (!empty($length) AND $length>0){
			if(socket_recv($this->_socket, $data, $length, MSG_WAITALL ) === FALSE)
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
			
				return new SpykeeResponse(self::STATE_ERROR, SpykeeResponse::UNABLE_READ_DATA);
			}
			echo 'Donnée reçue :'.$data."\r\n";
		}
		// TODO il serrait peut-être interessant de différencier les réponses pour détecter un problème

		return new SpykeeResponse(self::STATE_OK, $idDescription, $data);
	}


	protected function closeSocket(){
		@socket_close($this->_socket);
		@fclose($this->_stream);
	}
	
	public function move($left, $right){
		$left = ($left < 256) ? $left : 255;
		$left = ($left >= 0) ? $left : 0;
		$right = ($right < 256) ? $right : 255;
		$right = ($right >= 0) ? $right : 0;
		return $this->sendPacketToController(self::MOVE, pack('CC', $left, $right));
	}

	public function left(){
		return $this->sendPacketToController(self::LEFT);
	}

	public function right(){
		return $this->sendPacketToController(self::RIGHT);
	}

	public function forward(){
		return $this->sendPacketToController(self::FORWARD);
	}

	public function back(){
		return $this->sendPacketToController(self::BACK);
	}

	public function stop(){
		return $this->sendPacketToController(self::STOP);
	}
	
	public function holdingLeft(){
		return $this->sendPacketToController(self::HOLDING_LEFT);
	}
	
	public function holdingRight(){
		return $this->sendPacketToController(self::HOLDING_RIGHT);
	}
	
	public function holdingForward(){
		return $this->sendPacketToController(self::HOLDING_FORWARD);
	}
	
	public function holdingBack(){
		return $this->sendPacketToController(self::HOLDING_BACK);
	}

	public function activate(){
		return $this->sendPacketToController(self::ACTIVATE);
	}

	public function chargeStop(){
		return $this->sendPacketToController(self::CHARGE_STOP);
	}

	public function dock(){
		return $this->sendPacketToController(self::DOCK);
	}

	public function dockCancel(){
		return $this->sendPacketToController(self::DOCK_CANCEL);
	}

	public function sendMp3(){
		return $this->sendPacketToController(self::SEND_MP3);
	}
	
	public function audioPlay(){
		return $this->sendPacketToController(self::AUDIO_PLAY);
	}
	
	public function wirelessNetworks(){
		$request = $this->sendPacketToController(self::WIRELESS_NETWORKS);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}
	
	public function getLog(){
		$request = $this->sendPacketToController(self::GET_LOG);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}

	public function getConfig(){
		$request = $this->sendPacketToController(self::GET_CONFIG);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}
	
	public function getPowerLevel(){
		$request = $this->sendPacketToController(self::GET_POWER_LEVEL);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}
	
	public function refreshPowerLevel(){
		$request = $this->sendPacketToController(self::REFRESH_POWER_LEVEL);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}

	public function stopServer(){
		return $this->sendPacketToController(self::STOP_SERVER);
	}

	function __destruct(){
		$this->closeSocket();
	}


}


?>