<?php
require_once(PATH.'libs/spykee-controller/controller.php');

class SpykeeControllerClient{

	/*
	 * Définition des attributs
	*/
	private $_serverPort;
	private $_robotName;
	private $_serverIp;
	private $_sock;

	function __construct($robotName, $serverIp, $serverPort){
		$this->_serverPort = $serverPort;
		$this->_robotName = $robotName;
		$this->_serverIp = $serverIp;
		$this->connectToTheController();
	}


	protected function connectToTheController(){
		//Creation of the socket
		if(!($this->_sock = socket_create(AF_INET, SOCK_STREAM, 0))){ //Can't connect
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Couldn't create socket: [$errorcode] $errormsg \n");
		}

		echo "Socket created \n";                            //Connected

		if(!socket_connect($this->_sock, $this->_serverIp, $this->_serverPort)){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Could not connect: [$errorcode] $errormsg \n");
		}

		echo "Connection established \n";
		$return = $this->getResponse();
	}


	protected function sendAction($message){

		if(!socket_send($this->_sock, $message, strlen($message), 0)){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Could not send data: [$errorcode] $errormsg \n");
		}

		echo "Message send successfully \n";
		return $this->getResponse();
	}

	protected function getResponse(){
		if(socket_recv($this->_sock, $response, SpykeeController::PAQUET_HEADER_SIZE, MSG_WAITALL) === FALSE ){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Could not send data: [$errorcode] $errormsg \n");
		}
		$response = bin2hex($response);
		echo 'Reponse : '.$response;
		$header = hex2bin($response[0].$response[1].$response[2].$response[3].$response[4].$response[5]);
		$type = base_convert($response[6].$response[7], 16, 10);
		$state = base_convert($response[8].$response[9], 16, 10);
		$length = base_convert($response[10].$response[11].$response[12].$response[13], 16, 10);
		echo ' Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n";

		if (!empty($length) AND $length>0){
			if(socket_recv($this->_sock, $data, $length, MSG_WAITALL ) === FALSE)
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
				// TODO : Utiliser le gestionnaire d'erreur du site
				die('Impossible de lire des donnée renvoyée ['.$errorCode.'] '.$errorMsg."\r\n");
				return SpykeeController::STATE_ERROR;
			}
			echo 'Donnée reçue :'.$data."\r\n";
		}

		return $response;
	}


	protected function closeSocket(){
		socket_close($this->_sock);
		/*if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
		 {

		die("Socket has been succefully closed \n");
		}*/

	}


	public function turnLeft(){ //Tourne a gauche
		$this->sendAction(SpykeeController::TURN_LEFT);
	}

	public function turnRight(){ //Tourne a droite
		$this->sendAction(SpykeeController::TURN_RIGHT);
	}

	public function forward(){  //Tout droit
		$this->sendAction(SpykeeController::FORWARD);
	}

	public function back(){  //en Arriere
		$this->sendAction(SpykeeController::BACK);
	}

	public function stop(){
		$this->sendAction(SpykeeController::STOP);
	}

	public function activate(){
		$this->sendAction(SpykeeController::ACTIVATE);
	}

	public function charge_stop(){
		$this->sendAction(SpykeeController::CHARGE_STOP);
	}

	public function dock(){
		$this->sendAction(SpykeeController::DOCK);
	}

	public function dock_cancel(){
		$this->sendAction(SpykeeController::DOCK_CANCEL);
	}

	public function wireless_networks(){
		$this->sendAction(SpykeeController::WIRELESS_NETWORKS);
	}

	public function get_log(){
		$this->sendAction(SpykeeController::GET_LOG);
	}

	public function get_config(){
		$this->sendAction(SpykeeController::GET_CONFIG);
	}

	public function stopServer(){

	}

	function __destruct(){
		$this->closeSocket();
	}


}


?>