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
		$this->_sock = pfsockopen('tcp://'.$this->_serverIp, $this->_serverPort, $errorCode, $errorMsg, SpykeeController::CLIENT_SERVER_TIMEOUT);
		if ($this->_sock === FALSE){
			// TODO : Utiliser le gestionnaire d'erreur du site
			die("Couldn't create socket: [$errorcode] $errormsg \n");
		}
		echo "Connection established \n";
	}


	protected function sendAction($message){
		if (fwrite($this->_sock, $message) === FALSE){
			// TODO : Utiliser le gestionnaire d'erreur du site
			die('Impossible d\'envoyer des données');
		}
		
		echo "Message send successfully \n";
		//return $this->getResponse();
	}

	protected function getResponse(){
		// Récupération des données envoyées
		$response = fread($this->_sock, SpykeeController::PAQUET_HEADER_SIZE);
		if ($response === FALSE){
			// TODO : Utiliser le gestionnaire d'erreur du site
			die('Impossible de lire l\'entête');
		}
		
		$response = bin2hex($response);
		echo 'Reponse : '.$response;
		$header = hex2bin($response[0].$response[1].$response[2].$response[3].$response[4].$response[5]);
		$type = base_convert($response[6].$response[7], 16, 10);
		$state = base_convert($response[8].$response[9], 16, 10);
		$length = base_convert($response[10].$response[11].$response[12].$response[13], 16, 10);
		echo ' Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n";

		if (!empty($length) AND $length>0){
			$data = fread($this->_sock, $length);
			if ($data === FALSE){
				// TODO : Utiliser le gestionnaire d'erreur du site
				die('Impossible de lire les données');
			}
			echo 'Donnée reçue :'.$data."\r\n";
		}

		return $response;
	}


	protected function closeSocket(){
		fclose($this->_sock);
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

	public function send_mp3(){
		$this->sendAction(SpykeeController::SEND_MP3);
	}
	
	public function audio_play(){
		$this->sendAction(SpykeeController::AUDIO_PLAY);
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
		//$this->closeSocket();
	}


}


?>