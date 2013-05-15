<?php
class SpykeeConfig {
	private $_data=array();
	
	public function __construct($file){
		
	}
	
	private function loadIniFile($file){
		if (!is_file($file)){
			
		}
		else{
			$array = parse_ini_file(PATH.'configs/website.ini', true);
			foreach ($array as $sectionName => $section){
				$this->_data[$sectionName] = new SpykeeConfigSection($section);
			}
		}
	}
}

class SpykeeConfigSection {
	private $_data=array();

	public function __construct($section){
		$this->_data = $section;
	}

	function __get($name){
		if (array_key_exists($name, $this->_data))
			return $this->_data[$name];
		else{
			// TODO utiliser un gestionnaire d'erreur
			$trace = debug_backtrace();
			trigger_error(
			'Propriété non-définie via __get() : ' . $name .
			' dans ' . $trace[0]['file'] .
			' à la ligne ' . $trace[0]['line'],
			E_USER_NOTICE);
			return null;
		}
	}
}

?>