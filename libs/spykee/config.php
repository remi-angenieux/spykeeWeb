<?php
/**
 * Content all methodes used to manage Configuration
 * @author Remi ANGENIEUX
 */
class SpykeeConfig {
	protected $_data=array();
	protected $_robotInfo=array();
	protected $_errorManager;
	
	const DEFAULT_ROBOT_NAME = 'Unknow Robot';
	const DEFAULT_ROBOT_IP = '0.0.0.0';
	
	/**
	 * Load and parse an ini file
	 * @param string $file File name of ini file
	 * @param array $robotInfo Array wich content "ip" and "name" of the robot
	 */
	public function __construct($file, $robotInfo){
		$this->_setRobotInfo($robotInfo);
		// Load default configuration
		$default = $this->_loadIniFile(PATH.'configs/default/'.$file, true);
		// Load user configuration
		$user = $this->_loadIniFile(PATH.'configs/'.$file);
		// Overwrite default config with the user config. Doesn't add user config not set in the default config
		$config = ($user !== false) ? array_intersect_key($user + $default, $default) : $default ;
		$this->_parseConfig($config);
		
		$this->_errorManager = new SpykeeError($this->_robotInfo['name'], $this->_robotInfo['ip'], $this);
	}
	
	/**
	 * Tests if the object has the informations required
	 * @param array $array Array wich content ip and name of the robot
	 */
	protected function _setRobotInfo($array){
		if(!is_array($array)){
			$this->_robotInfo['name'] = self::DEFAULT_ROBOT_NAME;
			$this->_robotInfo['ip'] = self::DEFAULT_ROBOT_IP;
			// Send error with 2 methodes because it's critical programming error
			$trace = debug_backtrace();
			$errorMessage = 'Argument 2 for SpykeeConfig::__construct() have to be an array, called in'
					.$trace[0]['file'].' on line '.$trace[0]['line'];
			SpykeeError::standaloneError($errorMessage);
			trigger_error($errorMessage, E_USER_WARNING);
		}
		else{
			if (empty($array['name'])){
				$this->_robotInfo['name'] = self::DEFAULT_ROBOT_NAME;
				// Send error with 2 methodes because it's critical programming error
				$errorMessage = 'Argument 2 for SpykeeConfig::__construct() must have an index name, called in'
						.$trace[0]['file'].' on line '.$trace[0]['line'];
				SpykeeError::standaloneError($errorMessage);
				trigger_error($errorMessage, E_USER_WARNING);
			}
			if (empty($array['ip'])){
				$this->_robotInfo['ip'] = self::DEFAULT_ROBOT_IP;
				// Send error with 2 methodes because it's critical programming error
				$errorMessage = 'Argument 2 for SpykeeConfig::__construct() must have an index ip, called in'
						.$trace[0]['file'].' on line '.$trace[0]['line'];
				SpykeeError::standaloneError($errorMessage);
				trigger_error($errorMessage, E_USER_WARNING);
			}
		}
	}
	
	/**
	 * Return an associative array of the ini file
	 * @param string $file File name of the fil with is path
	 * @param bool $fatal This file is require to run the script ?
	 */
	protected function _loadIniFile($file, $fatal=false){
		if (!is_file($file)){
			if ($fatal)
				trigger_error('Default config file doesn\'t exist ('.$file.')', E_USER_ERROR);
			else
				SpykeeError::standaloneError('User config file doesn\'t exist ('.$file.')');
			return false;
		}
		else
			return parse_ini_file($file, true);
	}
	
	/**
	 * Convert the multidimensional array to an object
	 * @param array $config Array of all sections their config
	 */
	protected function _parseConfig($config){
		foreach ($config as $sectionName => $section)
			$this->_data[$sectionName] = new SpykeeConfigSection($section, $this->_robotInfo);
	}
	
	/**
	 * Returns an object of the corresponding config section
	 * @param string $name Section name
	 * @return multitype:SpykeeConfigSection|NULL
	 */
	public function __get($name){
		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];
		else{
			$trace = debug_backtrace();
			$this->_errorManager->error('Config : Section named '.$name.' undefined in '
					.$trace[0]['file'].' at the line '.$trace[0]['line']);
			return null;
		}
	}
}

class SpykeeConfigSection {
	private $_data=array();
	protected $_errorManager;
	protected $_robotInfo;

	/**
	 * Convert the array to an object
	 * @param array $section Array content all config
	 * @param array $robotInfo Array wich content ip and name of the robot
	 */
	public function __construct($section, $robotInfo){
		$this->_robotInfo = $robotInfo;
		$this->_errorManager = new SpykeeError($this->_robotInfo['name'], $this->_robotInfo['ip'], $this);
		$this->_data = $section;
	}
	
	/**
	 * Returns the content of the config
	 * @param string $name Config name
	 * @return multitype:mixed|NULL
	 */
	function __get($name){
		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];
		else{
			$trace = debug_backtrace();
			$this->_errorManager->error('Config : Config named '.$name.' undefined in '
					.$trace[0]['file'].' at the line '.$trace[0]['line']);
			return null;
		}
	}
}

?>