<?php
/**
 * Content all methodes to manage errors
 * @author Remi ANGENIEUX
 */
class SpykeeError {
	protected $_robotName;
	protected $_robotIp;
	protected $_configs;
	protected $_class;
	
	/**
	 * Prepare the error manager
	 * @param string $robotName Name of the robot
	 * @param string $robotIp IP Adresse of the robot
	 * @param SpykeeConfig $configs Config of the Object (Controller or Robot)
	 * @param string $class Name of the class
	 */
	public function __construct($robotName, $robotIp, $configs, $class){
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		$this->_configs = $configs;
		$this->_class = $class;
	}
	
	/**
	 * Send an error
	 * @param string $message Error message
	 * @param number $level Error level
	 */
	public function error($message, $level=1){
		$this->_writeLog($message, $level);
		// You can add custom methodes
	}
	
	/**
	 * Send the error in a log file
	 * @param string $message Error message
	 * @param number $level Erro level
	 */
	protected function _writeLog($message, $level){
		if ($this->_configs->errors->logLevel >= $level){
			$content = date($this->_config->errors->timeFormat, time());
			$content .= '@'.$this->_robotName.'('.$this->_robotIp.') : ';
			$content .= $txt;
	
			/*
			 * Create/Append file log
			*/
			$logFile=PATH.$this->_configs->pathLogFile.$this->_robotName.'-'.$this->_configs->logFile.'.log';
			if (!$file = fopen($logFile, 'a')){
				echo 'Unable to open the file log : '.$logFile."\r\n";
			}
			else {
				if (fwrite($file, $content) === FALSE ){
					echo 'Unable to write ine the file log : '.$logFile."\r\n";
				}
				fclose($file);
			}
		}
	}
}

class ExceptionSpykee extends Exception{
	/**
	 * Manage user errors
	 * @param string $message Message send to the user
	 * @param integer $code Error code
	 * @param Exception $previous Previous Exception
	 */
	public function __construct($message=null, $code=null, $previous=null){
		$this->manageError();
		
		parent::__construct($message, $code, $previous);
	}
	
	protected function manageError(){
		switch($this->file){
			case '':
				
			break;
			
			default:
				echo 'test';
			break;
		}
	}
}
$a = new ExceptionSpykee()

?>