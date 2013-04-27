<?php
// Inclue la configuration du robot. Et ses constantes partagées
require_once(PATH.'configs/spykeeRobot.php');
// Inclue l'objet utilisé lors des retours des différentes actions
require_once(PATH.'libs/spykee-robot/robotResponse.php');

class SpykeeClientRobot extends SpykeeConfigRobot {
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
	
	const SENDFILE_FLAG_NONE= 0;
	const SENDFILE_FLAG_BEGIN= 1;
	const SENDFILE_FLAG_END= 2;
	
	const FILE_ID_MUSIC= 64;
	const FILE_ID_FIRMWARE= 66;
	
	const ROBOT_PORT = 9000;
	
	/*
	 * Définition des attributs
	*/
	protected $_robotName;
	protected $_robotUsername;
	protected $_robotPassword;
	protected $_robotIp;
	protected $_robotSocket=NULL;
	protected $_robotStream=NULL;
	protected $_logFile;
	protected $_moveSpeed = 100;
	protected $_powerLevel = NULL;
	protected $_reconnection=0;

	function __construct($robotName, $robotIp, $robotUsername=self::DEFAULT_USERNAME, $robotPassword=self::DEFAULT_PASSWORD){
		date_default_timezone_set(self::TIME_ZONE); // Pour les dates des logs
		$this->_reconnection=0;
		// TODO vérifier les valeurs entrées avec un geter
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_robotUsername = $robotUsername;
		$this->_robotPassword = $robotPassword;
		$this->_logFile = realpath(__DIR__).'/../../logs/'.$this->_robotName.'-ClientRobot.log';
		
		$this->initSocket();
		$this->authentificationRobot();
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
	
	protected function initSocket(){
		$this->_robotStream = fsockopen('tcp://'.$this->_robotIp, self::ROBOT_PORT, $errorCode, $errorMsg, self::CONNECTION_ROBOT_TIMEOUT);
		
		if ($this->_robotStream === FALSE){
			$this->writeLog('Impossible de se connecter au robot : ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			die;
		}
		echo '<br />'.$this->_robotStream.'<br />';
		$this->_robotSocket = socket_import_stream($this->_robotStream);
	}
	
	protected function closeSocket(){
		@socket_close($this->_robotSocket);
		@fclose($this->_robotStream);
	}

	protected function authentificationRobot(){
		// Demande connexion au robot
		$this->sendPacketToRobot(self::PAQUET_TYPE_AUTH_REQUEST, $this->packString($this->_robotUsername).$this->packString($this->_robotPassword));
		// On attend que le robot valide la connexion
		$this->getResponse(); // Attente de l'envoie du numero du firmware
		$this->getResponse(); // Attente du niveau de batterie
	}

	protected function packString($str){
		return pack('Ca*', strlen($str), $str);
	}


	protected function sendPacketToRobot($type, $data=NULL){
		/*
		 * Envoie du paquet vers le robot
		*/
		$strlen=(!empty($data)) ? strlen($data) : 0;
		$msg = pack('a2Cn', 'PK', $type, $strlen);
		echo "\r\n".'Entête : '.bin2hex($msg);
		if ($strlen>0)
			$msg .= $data;
		if( !socket_send($this->_robotSocket, $msg, strlen($msg), MSG_DONTROUTE)){
			$errorCode = socket_last_error();
			$errorMsg = socket_strerror($errorCode);
			
			if ($errorCode == 32 ){ // Broken pipe. Et relance une connexion
				$this->_reconnection++;
				echo 'Broken pipe. Reconnexion...'."\r\n";
				// Si le nombre de tentative dépasse le nombre max de tentative
				if ($this->_reconnection >= self::NB_RECONNECTION){
					$this->writeLog('Impossible de se reconnecter au robot après '.$this->_reconnection.' tentatives.'."\r\n", 1);
					return new SpykeeResponse(self::STATE_ERROR, 'Nombre de tentative de reconnexion dépassé');
				}
				$this->initSocket();
				$this->authentificationRobot();
				$return = $this->sendPacketToRobot($type, $data);
				if ($return->getState() == self::STATE_OK) // Reinit du compteur
					$this->_reconnection=0;
				return $return;
			}
			else{
				$this->writeLog('Impossible d\'envoyer le paquet : "'.$msg.'". ['.$errorCode.'] '.$errorMsg."\r\n", 1);
				return new SpykeeResponse(self::STATE_ERROR, 'Impossible d\'envoyer le paquet');
			}
		}
		echo "\r\n".'Data : '.bin2hex($data);
		echo "\r\n".'Envoie de la trame :'.$msg.' ('.bin2hex($msg).')';
		$this->writeLog('Envoi vers le robot la trame : '.bin2hex($msg)."\r\n", 3);
		return new SpykeeResponse(self::STATE_OK, 'Le paquet à bien été envoyé');
	}

	protected function getResponse(){
		if(socket_recv($this->_robotSocket, $response, self::PAQUET_HEADER_SIZE, MSG_WAITALL ) === FALSE)
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
		
			$this->writeLog('Aucune trame de réponse retournée ['.$errorCode.'] '.$errorMsg."\r\n", 1);
			return new SpykeeResponse(self::STATE_ERROR, 'Problème de réception de la trame');
			$state = self::STATE_ERROR;
		}
		if (empty($response)){ // Reset de la connexion
			echo 'Reset connection. Reconnexion...';
			$this->initSocket();
			// TODO vérifier qu'une nouvelle authentification n'est pas requise
			//$this->authentificationRobot();
			return new SpykeeResponse(self::STATE_ERROR, 'Connexion réinitialisée');
		}
		else{
			$response = bin2hex($response);
			echo '<br />Entête reçue : '.$response;
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
					return new SpykeeResponse(self::STATE_ERROR, 'Impossible de lire les données du paquet');
				}
			}
			$this->writeLog('Donnée transportée : '.bin2hex($data)."\r\n", 3);
	
			echo 'Donnée reçue :'.$data."\r\n";
			echo 'Donnée reçue :'.bin2hex($data)."\r\n";
	
			/*
			 * Gestion de la réponse
			*/
			$state = self::STATE_OK; // Etat par défaut
			switch($type){
				case self::PAQUET_TYPE_AUDIO:
					$description = 'Paquet reçu de type audio';
					break;
				case self::PAQUET_TYPE_VIDEO:
					$description = 'Paquet reçu de type vidéo';
					break;
				case self::PAQUET_TYPE_POWER:
					$description = 'Paquet reçu de type niveau de batterie';
					$this->_powerLevel = $data;
					break;
				case self::PAQUET_TYPE_AUTH_REPLY:
					// $data
					// 0001 = Connexion requise
					// 0003 = Deja connecté
					$description = 'Paquet reçu de type authentification';
					echo 'Auth Reply reçue'."\r\n";
					break;
				case self::PAQUET_TYPE_STOP:
					$description = 'Paquet reçu de type stop';
					break;
				case self::PAQUET_TYPE_WIRELESS_NETWORKS:
					$description = 'Paquet reçu de type connexion sans fil';
					break;
				case self::PAQUET_TYPE_CONFIG:
					$description = 'Paquet reçu de type configuration';
					break;
				case self::PAQUET_TYPE_LOG:
					$description = 'Paquet reçu de type log';
					break;
	
				default:
					echo 'Paquet non reconnu'."\r\n";
					$this->writeLog('Paquet inconnu reçu avec comme type : '.$type."\r\n", 1);
					$state = self::STATE_ERROR;
					$description = 'Paquet reçu non reconnu';
					break;
			}
		}

		return new SpykeeResponse($state, $description, $data);
	}

	public function move($left, $right){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_MOVE, pack('CC', $left, $right));
		//return $this->sendPacketToRobot(self::PAQUET_TYPE_MOVE, $left. $right);
	}

	public function left(){
		return $this->move(150, 100);
	}

	public function right(){
		return $this->move(100, 150);
	}

	public function forward(){
		return $this->move($this->_moveSpeed, $this->_moveSpeed);
	}

	public function back(){
		return $this->move(128 + $this->_moveSpeed, 128 + $this->_moveSpeed);
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
		$this->send_file($fileName, self::FILE_ID_MUSIC);
	}
	
	public function send_file($fileName, $file_id){
		$flag =self::SENDFILE_FLAG_BEGIN;
		print "Sending file $fileName\n";
		$fh=fopen($fileName,'r');
		$maxlen = self::PAQUET_DATA_SIZE_MAX - self::PAQUET_HEADER_SIZE;
		while ($contentlen = fread($fh, $content, $maxlen)) {
			if ($maxlen !=  $contentlen) {
				# End of file, set the end flag
				$flag | self::SENDFILE_FLAG_END;
			}
			$this->sendPacketToRobot(self::PAQUET_TYPE_FILE ,pack("CCA*", $file_id, $flag, $content));
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
		
	public function audio_play($idFile){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_PLAY, pack('C', $idFile));

	}

	public function audio_stop(){
		return $this->sendPacketToRobot(self::PAQUET_TYPE_STOP);
	}

	public function wireless_networks(){
		$request = $this->sendPacketToRobot(self::PAQUET_TYPE_WIRELESS_NETWORKS);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}

	public function get_log(){
		$request = $this->sendPacketToRobot(self::PAQUET_TYPE_LOG);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}

	public function get_config(){
		$request = $this->sendPacketToRobot(self::PAQUET_TYPE_CONFIG);
		if ($request->getState() == self::STATE_OK) // Si le paquet à bien été envoyé
			return $this->getResponse();
		else
			return $request;
	}

	public function get_power_level(){
		if ($this->_powerLevel != NULL) // Si le niveau de batterie à été récupérée au moins une fois
			return new SpykeeResponse(self::STATE_OK, 'Niveau de batterie bien recupéré', $this->_powerLevel);
		else
			return $this->refresh_power_level();
	}

	public function refresh_power_level(){
		$request = $this->sendPacketToRobot(self::PAQUET_TYPE_POWER);
		if ($request->getState() == self::STATE_OK){ // Si le paquet à bien été envoyé
			$response = $this->getResponse();
			$this->_powerLevel = $response->getData();
			return $response;
		}
		else
			return $request;
	}
	
	public function __destruct(){
		$this->closeSocket();
	}
}

?>