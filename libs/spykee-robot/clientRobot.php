<?php
require_once 'spykee-controller/controller.php';

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
	const PAQUET_TYPE_FILE = 6;
	const PAQUET_TYPE_PLAY = 7;
	const PAQUET_TYPE_STOP = 8;
	const PAQUET_TYPE_AUTH_REQUEST = 10;
	const PAQUET_TYPE_AUTH_REPLY = 11;
	const PAQUET_TYPE_CONFIG  = 13;
	const PAQUET_TYPE_WIRELESS_NETWORKS = 14;
	const PAQUET_TYPE_STREAMCTL = 15;
	const PAQUET_TYPE_ENGINE = 16;
	const PAQUET_TYPE_LOG = 17;
	
	const MESSAGE_TYPE_BASE_FIND=6;
	const MESSAGE_TYPE_ACTIVATE=1;
	const MESSAGE_TYPE_CHARGE_STOP=5;
	const MESSAGE_TYPE_BASE_FIND_CANCEL = 7;
	
	const SENDFILE_FLAG_NONE= 0;
	const SENDFILE_FLAG_BEGIN= 1;
	const SENDFILE_FLAG_END= 2;
	
	const FILE_ID_MUSIC= 64;
	const FILE_ID_FIRMWARE= 66;
	
	/*
	 * Définition des attributs
	*/
	protected $_robotName;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_robotIp;
	protected $_robotSocket=NULL;
	protected $_logFile;
	protected $_moveSpeed = 5;
	protected $_powerLevel = NULL;
	
	function __construct($robotName, $robotIp, $robotUsername=SpykeeController::DEFAULT_USERNAME, $robotPassword=SpykeeController::DEFAULT_PASSWORD){
		date_default_timezone_set(SpykeeController::TIME_ZONE); // Pour les dates des logs
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'-ClientRobot.log';
		
		$this->connectionToTheRobot();
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
		return pack('Ca*', strlen($str), $str);
	}
	
	
	protected function sendPacketToRobot($type, $data=NULL){
		/*
		 * Envoie du paquet vers le robot
		*/
		$strlen=(isset($data)) ? strlen($data) : 0;
		$msg = pack('a2Cn', 'PK', $type, $strlen);
		if ($strlen>0)
			$msg .= $data;
		if( !socket_send($this->_robotSocket, $msg, strlen($msg), MSG_DONTROUTE)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
				
			$this->writeLog('Impossible d\'envoyer le paquet : "'.$msg.'". ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			return SpykeeController::STATE_ERROR;
		}
		$this->writeLog('Envoi vers le robot la trame : '.bin2hex($msg)."\r\n", 3);
		
		return $this->getResponse($type);
	}
	
	protected function getResponse($requestType){
		if(socket_recv($this->_robotSocket, $response, self::PAQUET_HEADER_SIZE, MSG_WAITALL ) === FALSE)
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
		
			$this->writeLog('Aucune trame de réponse retournée ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			return SpykeeController::STATE_ERROR;
		}
		$response = bin2hex($response);
		$header = hex2bin($response[0].$response[1].$response[2].$response[3]);
		$type = base_convert($response[4].$response[5], 16, 10);
		$length = base_convert($response[6].$response[7].$response[8].$response[9], 16, 10);
		
		$this->writeLog('Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n", 3);
		echo ' Paquet reçue : header='.$header.', type='.$type.', len='.$length."\r\n";
		
		if (!empty($length) AND $length>0){
			if(socket_recv($this->_robotSocket, $data, $length, MSG_WAITALL ) === FALSE)
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
					
				$this->writeLog('Impossible de lire des donnée renvoyée ['.$errorCode.'] '.$errorMsg."\r\n", 1);
				return SpykeeController::STATE_ERROR;
			}
		}
		$this->writeLog('Donnée transportée : '.bin2hex($data)."\r\n", 3);
		
		echo 'Donnée reçue :'.$data."\r\n";
		
		/*
		 * Gestion de la réponse
		*/
		$return = SpykeeController::STATE_OK; // En attente de tout coder
		switch($type){
			case self::PAQUET_TYPE_AUDIO:
		
				break;
			case self::PAQUET_TYPE_VIDEO:
		
				break;
			case self::PAQUET_TYPE_POWER:
				$this->_powerLevel = $data;
				$return = $this->_powerLevel;
				break;
			case self::PAQUET_TYPE_AUTH_REPLY:
				if ($requestType == self::PAQUET_TYPE_AUTH_REQUEST)
					$return = SpykeeController::STATE_OK;
				else
					$return = SpykeeController::STATE_ERROR;
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
				$this->writeLog('Paquet inconnu reçu avec comme type : '.$type."\r\n", 1);
				$return = SpykeeController::STATE_ERROR;
				break;
		}
		
		return $return;
	}
	
	public function move($left, $right){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_MOVE, pack('CC', $left, $right));
	}
	

	public function left(){
		return $this->move(140, 110);
	}
	
	public function right(){
		return $this->move(110, 140);
	}
	
	public function forward(){
		return $this->move(40- $this->_moveSpeed, 40 - $this->_moveSpeed);
	}
	
	public function back(){
		return $this->move(125 + $this->_moveSpeed, 125 + $this->_moveSpeed);
	}
	
	public function stop(){
		return $this->move(0,0);
	}
	
	public function activate(){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_ENGINE , pack('C',self::MESSAGE_TYPE_ACTIVATE));
	}
	
	public function charge_stop(){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_ENGINE , pack("C", self::MESSAGE_TYPE_CHARGE_STOP));
	}
	
	public function dock(){
		$this->sendPacketToRobot(self::PAQUET_TYPE_ENGINE , pack("C", self::MESSAGE_TYPE_BASE_FIND));
	}
	
	public function dock_cancel(){
		$this->sendPacketToRobot(self::PAQUET_TYPE_ENGINE , pack("C", self::MESSAGE_TYPE_BASE_FIND_CANCEL));
	}
<<<<<<< HEAD
	
	public function send_mp3(){
	$this->send_file("music.mp3", self::FILE_ID_MUSIC);
=======

	public function send_mp3($fileName){
	$this->send_file($fileName, self::FILE_ID_MUSIC);
>>>>>>> parent of 52353da... Premier pas de Spykee via le web
	}
<<<<<<< HEAD

=======
	
	public function send_file($fileName, $file_id){
		 
			$flag =self::SENDFILE_FLAG_BEGIN;
			print "Sending file $fileName\n";
<<<<<<< HEAD
			$fh=fopen($fileName,'r',1);
			$maxlen = self::PAQUET_DATA_SIZE_MAX - self::PAQUET_HEADER_SIZE;
			while ($contentlen= strlen($content=fread($fh, $maxlen))) {
			/*if ($maxlen !=  $contentlen) {
=======
			$fh=fopen($fileName,'r');
			$maxlen = self::PAQUET_DATA_SIZE_MAX - self::PAQUET_HEADER_SIZE;
			while ($contentlen = fread($fh, $content, $maxlen)) {
			if ($maxlen !=  $contentlen) {
>>>>>>> parent of 52353da... Premier pas de Spykee via le web
			# End of file, set the end flag
				$flag | self::SENDFILE_FLAG_END;
			}*/
			$this->sendPacketToRobot(self::PAQUET_TYPE_FILE ,pack('CCA', $file_id, $flag, $content));
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
	
	
<<<<<<< HEAD
=======
>>>>>>> 02fd242890bdba5e36c723800d95d72be34a34e0
>>>>>>> parent of 52353da... Premier pas de Spykee via le web
	public function audio_play($idFile){
		if ($this->sendPacketToRobot(self::PAQUET_TYPE_PLAY, pack('C',$idFile)))
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
	
	public function get_power_level(){
		return $this->_powerLevel;
	}
	
	public function refresh_power_level(){
		$this->_powerLevel = $this->sendPacketToRobot(self::PAQUET_TYPE_POWER);
		return $this->_powerLevel;
	}
}

?>
