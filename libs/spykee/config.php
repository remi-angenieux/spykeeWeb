<?php
/**
 * Content all methodes used to manage Configuration
 * @author Remi ANGENIEUX
 */
class SpykeeConfig {
	protected $_data=array();
	protected $_robotInfo=array();
	
	const DEFAULT_ROBOT_NAME = 'Unknow Robot';
	const DEFAULT_ROBOT_IP = '0.0.0.0';
	
	/**
	 * Load and parse an ini file
	 * @param string $file File name of ini file
	 */
	public function __construct($file){
		// Load default configuration
		$default = $this->_loadIniFile(PATH.'configs/default/'.$file);
		// Load user configuration
		$user = $this->_loadIniFile(PATH.'configs/'.$file);
		// Overwrite default config with the user config. Doesn't add user config not set in the default config
		$config = ($user !== false) ? array_intersect_key($user + $default, $default) : $default ;
		$this->_parseConfig($config);
	}
	
	/**
	 * Return an associative array of the ini file
	 * @param string $file File name of the fil with is path
	 * @return bool|array
	 */
	protected function _loadIniFile($file){
		if (!is_file($file)){
			throw new ExceptionSpykee('Unable to launch Spykee Script', 'Config file doesn\'t exist ('.$file.')');
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
			$this->_data[$sectionName] = new SpykeeConfigSection($section, $this->_robotInfo, $this);
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
			echo $name;
			echo $trace[0]['file'].' at the line '.$trace[0]['line'];
			$errorMessage = 'Config: Section named '.$name.' undefined in '
					.$trace[0]['file'].' at the line '.$trace[0]['line'];
			SpykeeError::standaloneError($errorMessage);
			trigger_error($errorMessage, E_USER_ERROR);
			return null;
		}
	}
}

class SpykeeConfigSection {
	private $_data=array();

	/**
	 * Convert the array to an object
	 * @param array $section Array content all config
	 * @param array $robotInfo Array wich content ip and name of the robot
	 */
	public function __construct($section, $robotInfo, $test){
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
			$errorMessage = 'Config: Config named '.$name.' undefined in '
					.$trace[0]['file'].' at the line '.$trace[0]['line']; 
			SpykeeError::standaloneError($errorMessage);
			trigger_error($errorMessage, E_USER_ERROR);
			return null;
		}
	}
}

?>