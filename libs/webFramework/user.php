<?php
/*
 * Objet gerant la connexion persistante des membres
 */
class User {
	private static $_singleton;
	protected $_id;
	protected $_data=array();
	
	
	private function __construct(){
		$this->getId();
		$this->getInfo();
	}
	
	protected function getId()
	{
		if (empty($_SESSION['id']))
			$this->_id = Config::getInstance()->global->guestId;
		else if (is_numeric($_SESSION['id']) AND $_SESSION['id'] > Config::getInstance()->global->guestId)
			$this->_id = $_SESSION['id'];
		else
			$this->_id = Config::getInstance()->global->banId;
	}
	protected function getInfo(){
		$db = Db::getInstance()->db();
		$query = $db->prepare('SELECT id, pseudo, password, email FROM members WHERE id=?');
		$query->execute(array($this->_id));
		$this->_data = $query->fetch(PDO::FETCH_ASSOC);
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

?>