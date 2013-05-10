<?php
/*
 * Objet gérant les différentes configurations
 * /!\ Il doit OBLIGATOIREMENT être appelé AVANT l'objet Db /!\
 */
class Config {
	private static $_singleton=null;
	private $_data=array();
	private $_tempArray=array();

	private function __construct(){
		$this->loadIniFile();
		$this->loadDb();
		$this->parseConfig();
	}

	private function loadIniFile(){
		$array = parse_ini_file(PATH.'configs/website.ini', true);
		$this->_tempArray = array_merge_recursive($this->_tempArray, $array);
	}
	
	private function loadDb(){
		$query = Db::getInstance($this->_tempArray['database'])->db()->query('SELECT section, name, data FROM configs');
		$response = $query->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);
		
		// Réorganisation du tableau multidimensionnel
		$arrayContent=array();
		$arraySections=array();
		// Navigue dans les sections
		foreach($response as $sectionName => $section){
			foreach($section as $content){
				$arrayContent[$content['name']] = $content['data'];
			}
			$arraySections[$sectionName] = $arrayContent;
			$arrayContent=array();
		}
		$this->_tempArray = array_merge_recursive($this->_tempArray, $arraySections);
	}
	
	private function parseConfig(){
		foreach ($this->_tempArray as $sectionName => $section){
			$this->_data[$sectionName] = new ConfigSection($section);
		}
		unset($this->_tempArray);
	}

	static function getInstance() {
		if(is_null (self::$_singleton) ) {
			self::$_singleton = new self;
		}
		return self::$_singleton;
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

class ConfigSection{
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