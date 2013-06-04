<?php
/*
 * Objet gérant la connexion à la base de donnée
 * /!\ Il doit être OBLIGATOIREMENT appelé APRES l'objet Config /!\
 */
class Db{
	private static $_singleton;
	protected $_db;
	
	private function __construct($array){
		$this->connection($array);
	}
	
	static function getInstance($array='') {
		if(is_null (self::$_singleton) ) {
			self::$_singleton = new self($array);
		}
		return self::$_singleton;
	}
	
	protected function connection($conf){
		$dsn = $conf['sgbd'].':';
		$dsn .= 'host='.$conf['host'].';';
		$dsn .= 'port='.$conf['port'].';';
		$dsn .= 'dbname='.$conf['database'].';';
		$dsn .= 'user='.$conf['user'].';';
		$dsn .= 'password='.$conf['password'].';';
	
		try{
			$this->_db = new PDO($dsn, $conf['user'], $conf['password'], array(PDO::ATTR_PERSISTENT => $conf['persistent']));
			$this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->_db->query("SET NAMES '".$conf['charset']."'");
		}
		catch (PDOException $e) {
			// TODO utiliser un gestionnaire d'erreur
			print "Erreur !: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	
	public function db(){
		return $this->_db;
	}
}

?>