<?php
/**
 * Content all methodes used to manage errors
 * @author Remi ANGENIEUX
 */
class SpykeeError {
	protected $_robotName;
	protected $_robotIp;
	protected $_configs;
	protected $_class;
	
	const DEFAULT_DATE_FORMAT = 'Y-m-d \a\t hA';
	
	/**
	 * Prepare the error manager
	 * @param string $robotName Name of the robot
	 * @param string $robotIp IP Adresse of the robot
	 * @param SpykeeConfig $configs Config of the Object (Controller or Robot)
	 */
	public function __construct($robotName, $robotIp, $configs){
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_configs = $configs;
		date_default_timezone_set($configs->errors->timeZone); // Used for log
	}
	
	/**
	 * Send an error
	 * @param string $message Error message
	 * @param integer $level Error level
	 */
	public function error($message, $level=1){
		$this->_writeLog($message, $level);
		// You can add custom methodes
	}
	
	/**
	 * Send the error in a log file
	 * @param string $message Error message
	 * @param integer $level Erro level
	 */
	protected function _writeLog($message, $level){
		if ($this->_configs->errors->logLevel >= $level){
			$content = date($this->_config->errors->timeFormat, time());
			$content .= '@'.$this->_robotName.'('.$this->_robotIp.') : ';
			$content .= $message;
			$content .= "\r\n";
	
			/*
			 * Create/Append file log
			*/
			$logFile=PATH.$this->_configs->pathLogFile.$this->_robotName.'-'.$this->_configs->logFile.'.log';
			if (!$file = fopen($logFile, 'a')){
				echo 'Unable to open the file log : '.$logFile."\r\n";
				trigger_error('Unable to open the file log : '.$logFile, E_USER_ERROR); // Send the error through PHP
			}
			else {
				if (fwrite($file, $content) === FALSE ){
					echo 'Unable to write ine the file log : '.$logFile."\r\n";
					trigger_error('Unable to write ine the file log : '.$logFile, E_USER_ERROR); // Send the error through PHP
				}
				fclose($file);
			}
		}
	}
	
	/**
	 * Error manager wich doesn't have dependency, design for SpykeeConfig and error occured before
	 * the init of SpykeeConfig. In other case use the classic methode.
	 * @param string $message Error message
	 */
	static function standaloneError($message){
		$logFile = PATH.'logs/spykeeScriptError.log';
		$content = date(self::DEFAULT_DATE_FORMAT, time());
		$content .= $message;
		$content .= "\r\n";
		
		if (!$file = fopen($logFile, 'a')){
			echo 'Unable to open the file log : '.$logFile."\r\n";
			trigger_error('Unable to open the file log : '.$logFile, E_USER_ERROR); // Send the error through PHP
		}
		else {
			if (fwrite($file, $content) === FALSE ){
				echo 'Unable to write ine the file log : '.$logFile."\r\n";
				trigger_error('Unable to write ine the file log : '.$logFile, E_USER_ERROR); // Send the error through PHP
			}
			fclose($file);
		}
	}
}

class ExceptionSpykee extends Exception{
	protected $_userMessage;
	/**
	 * Manage user errors
	 * @param string $userMessage Message send to the user
	 * @param string $AdminMessage Full message error, for admin
	 * @param integer $code Error code
	 * @param Exception $previous Previous Exception
	 */
	public function __construct($userMessage, $AdminMessage=null, $code=null, $previous=null){
		if (empty($AdminMessage)) $AdminMessage = $userMessage;
		$this->_userMessage = $userMessage;
		
		parent::__construct($AdminMessage, $code, $previous);
	}
}

?>