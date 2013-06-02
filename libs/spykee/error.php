<?php
/**
 * Content all methodes used to manage errors
 * @author Remi ANGENIEUX
 */
class SpykeeError {
	protected $_robotName;
	protected $_robotIp;
	protected $_config;
	protected $_class;
	
	const DEFAULT_DATE_FORMAT = 'Y-m-d \a\t h A';
	
	/**
	 * Prepare the error manager
	 * @param string $robotName Name of the robot
	 * @param string $robotIp IP Addresse of the robot
	 * @param SpykeeConfig $configs Config of the Object (Controller or Robot)
	 */
	public function __construct($robotName, $robotIp, $config){
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_config = $config;
		date_default_timezone_set($config->errors->timeZone); // Used for log
	}
	
	/**
	 * Send an error
	 * @param string $message Error message
	 * @param integer $level Error level
	 */
	public function error($message, $level=1){
		$this->writeLog($message, $level);
		// You can add custom methodes here
	}
	
	/**
	 * Send the error/informations in a log file
	 * @param string $message Error message
	 * @param integer $level Erro level
	 */
	public function writeLog($message, $level){
		if ($this->_config->errors->logLevel >= $level){
			$content = date($this->_config->errors->timeFormat, time());
			$content .= '@'.$this->_robotName.'('.$this->_robotIp.'): ';
			$content .= $message;
			$content .= "\r\n";
	
			/*
			 * Create/Append file log
			*/
			$logFile=$this->_config->errors->pathLogFile.$this->_robotName.'-'.$this->_config->errors->logFileName.'.log';
			if (!$file = fopen($logFile, 'a')){
				//trigger_error('Unable to open the file log: '.$logFile, E_USER_ERROR); // Send the error through PHP
				self::standaloneError('Unable to open the file log: '.$logFile);
			}
			else {
				if (fwrite($file, $content) === FALSE ){
					//trigger_error('Unable to write in the file log: '.$logFile, E_USER_ERROR); // Send the error through PHP
					self::standaloneError('Unable to write in the file log: '.$logFile);
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
		$content .= ': ';
		$content .= $message;
		$content .= "\r\n";
		
		if (!$file = fopen($logFile, 'a'))
			trigger_error('Unable to open the file log: '.$logFile, E_USER_ERROR); // Send the error through PHP
		else {
			if (fwrite($file, $content) === FALSE )
				trigger_error('Unable to write in the file log: '.$logFile, E_USER_ERROR); // Send the error through PHP
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
	
	/**
	 * To get the user message
	 * @return string
	 */
	public function getUserMessage(){
		return $this->_userMessage;
	}
}

?>