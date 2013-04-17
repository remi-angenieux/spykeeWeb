<?php
require_once('controller.php');

class SpykeeClientRobot {
	/*
	 * Types des Socket
	 */
	const PAQUET_HEADER_SIZE = 5;
	const PAQUET_DATA_SIZE_MAX = 32768; //32*1024
	
	const PAQUET_TYPE_AUDIO = 1;
	const PAQUET_TYPE_VIDEO = 2;
	const PAQUET_TYPE_POWER = 3;
	const PAQUET_TYPE_MOVE = 5;
	const PAQUET_TYPE_FILE =  6;
	const PAQUET_TYPE_PLAY = 7;
	const PAQUET_TYPE_STOP = 8;
	const PAQUET_TYPE_AUTH_REQUEST = 10;
	const PAQUET_TYPE_AUTH_REPLY = 11;
	const PAQUET_TYPE_CONFIG  = 13;
	const PAQUET_TYPE_WIRELESS_NETWORKS = 14;
	const PAQUET_TYPE_STREAMCTL = 15;
	const PAQUET_TYPE_ENGINE = 16;
	const PAQUET_TYPE_LOG = 17;
	
	/*
	 * Définition des attributs
	*/
	protected $_robotName;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_robotIp;
	protected $_robotSocket=NULL;
	protected $_logFile;
	
	function __construct($robotName, $robotIp, $robotUsername, $robotPassword){
		date_default_timezone_set(SpykeeController::TIME_ZONE); // Pour les dates des logs
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'-ClientRobot.log';
		
		//$this->connectionToTheRobot();
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
	
	protected function connectionToTheRobot(){
	
		/*
		 * Debut de la session TCP
		*/
		if(!($this->_robotSocket = socket_create(AF_INET, SOCK_STREAM, 0)))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
	
			$this->writeLog('Impossible de créer le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}
	
		if( !socket_connect($this->_robotSocket, $this->_robotIp, SpykeeController::ROBOT_PORT) )
		{
			echo $this->_robotIp.':'.SpykeeController::ROBOT_PORT;
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
	
			$this->writeLog('Impossible de lier le socket : ['.$errorcode.'] '.$errormsg."\r\n", 1);
			die;
		}
	
		// Demande connexion au robot
		$this->sendPacketToRobot(self::PAQUET_TYPE_AUTH_REQUEST, $this->packString($this->_robotUsername).$this->packString($this->_robotPassword));
	
	}
	
	protected function packString($str){
		return pack('Ca*', count($str), $str);
	}
	
	protected function sendPacketToRobot($type, $data){
		/*
		 * Envoie du paquet vers le robot
		*/
		$msg = pack('a2Cn', 'PK', $type, count($data));
		$msg .= $data;
		echo $msg;
		if( !socket_send($this->_robotSocket, $msg, count($msg), MSG_DONTROUTE)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
				
			$this->writeLog('Impossible d\'envoyer le paquet : "'.$msg.'". ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			return FALSE;
		}
		$this->writeLog('Envoi vers le robot la trame : '.trim($msg)."\r\n", 3);
	
		/*
		 * Reception de la réponse du Robot
		*/
		if(socket_recv($this->_robotSocket, $response, self::PAQUET_HEADER_SIZE, MSG_WAITALL ) === FALSE)
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
				
			die("Could not receive data: [$errorcode] $errormsg \n");
			return FALSE;
		}
		$robotResponse = unpack('a2Cn', $response);
		$header = $robotResponse[1];
		$type = $robotResponse[2];
		$length = $robotResponse[3];
	
		$this->writeLog('Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n", 3);
		echo ' Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n";
	
		if (!empty($length) AND $length>0){
			if(socket_recv($this->_robotSocket, $data, $length, MSG_WAITALL ) === FALSE)
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
					
				die("Could not receive data: [$errorcode] $errormsg \n");
				return FALSE;
			}
		}
	
		echo 'Donnée reçue :'.$data."\r\n";
	
		/*
		 * Gestion de la réponse
		*/
		switch($type){
			case self::PAQUET_TYPE_AUDIO:
	
				break;
			case self::PAQUET_TYPE_VIDEO:
	
				break;
			case self::PAQUET_TYPE_POWER:
	
				break;
			case self::PAQUET_TYPE_AUTH_REPLY:
				echo 'Auth Reply reçue'."\r\n";
				break;
			case self::PAQUET_TYPE_STOP:
	
				break;
			case self::PAQUET_TYPE_WIRELESS_NETWORKS:
	
				break;
			case self::PAQUET_TYPE_CONFIG:
	
				break;
			case self::PAQUET_TYPE_LOG:
	
				break;
	
			default:
				echo 'Paquet non reconnu'."\r\n";
				$this->writeLog('(Robot) Paquet inconnu reçu avec comme type : '.$type, 1);
				break;
		}
	}
	
	public function move($left, $right){
	
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_MOVE, pack('CC', $left, $right)))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
	
	public function left(){
	
		return $this->move(140, 110);
	
	}
	
	public function right(){
	
		return $this->move(110, 140);
	
	}
	
	public function forward(){
	
	}
	
	public function back(){
	
	}
	
	public function stop(){
		return $this->move(0,0);
	}
	
	public function activate(){
	}
	
	public function charge_stop(){
	}
	
	public function dock(){
	}
	
	public function dock_cancel(){
	}
	
	public function send_mp3($fileName){
	}
	
	public function audio_play($idFile){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_PLAY, pack('C', $idFile)))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
	
	public function audio_stop(){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_STOP))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
	
	public function wireless_networks(){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_WIRELESS_NETWORKS))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
	
	public function get_log(){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_LOG))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
	
	public function get_config(){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_CONFIG))
			return SpykeeController::STATE_OK;
		else
			return SpykeeController::STATE_ERROR;
	}
}

?>