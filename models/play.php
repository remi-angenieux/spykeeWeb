<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	public $_input;

	//data passed to the home index view
	public function index(){


	}
	
	public function notConnected(){
		$message = 'Vous devez être connecté pour pouvoir jouer';
		$this->view->message('Vous n\' êtes pas connecté' , $message, '/account/login');
	}

	public function notAllowed(){
		$message='Vous n\'êtes pas autorisé a jouer';
		$this->view->message('Erreur' , $message, '/play');
	}
	
	//add member to queue
	public function enterQueue(){
		print "<div id=\"queue\">";
		$arr2=array();
		$arr3=array();
		$query = $this->db->prepare('INSERT INTO queue (refmember,timestamp) VALUES(?,?)') ;
		$query->execute(array($this->user->id,time()));
	}
	
	public function enterGame(){
		$dispo=$this->canPlay();
		$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?');
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)') ;
		$query->execute(array($this->user->id,$dispo,time()));
		$query = $this->db->prepare('UPDATE robots SET used=true WHERE robots.id=? ') ;
		$query->execute(array($dispo));
	}
	

	public function isInQueue(){                                    
			$query = $this->db->prepare('SELECT refmember FROM queue WHERE refmember=?');
			$query->execute(array($this->user->id));
			$result =$query->fetch(PDO::FETCH_ASSOC);
			$resultat=$result['refmember'];
			if ($resultat==null){
			return false;
			}
			else{
			return true;
			}
	} //Check if the current user is in the queue 
	

	public function isInGame(){
		$query = $this->db->prepare('SELECT refmember FROM games WHERE refmember=?');
		$query->execute(array($this->user->id));
		$result = $query->fetch(PDO::FETCH_ASSOC);
		$resultat=$result['refmember'];
		if ($resultat==null){
			return false;
		}
		else{
			return true;
		}
	}//Check if the current user is in the game 
	

	public function isFirst(){
		$query = $this->db->prepare('SELECT MIN(timestamp) FROM queue'); 
		$query->execute();
		$tab1 =$query->fetch(PDO::FETCH_ASSOC);
		$prem=$tab1['min'];
		$query = $this->db->prepare('SELECT refmember FROM queue WHERE timestamp =?');
		$query->execute(array($prem));
		$result =$query->fetch(PDO::FETCH_ASSOC);
		$resultat=$result['refmember'];
		if ($this->user->id==$resultat){
			return true;
		}
		else{
			return false;
		}
	}  //Check if the current user is the 1st of the queue


	public function leaveQueue(){
			$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?') ;
			$query->execute(array($this->user->id));
			$message='Vous avez bien été enlevé de la file';
			$this->view->message('File quittée' , $message, '/play');
	}
	
	public function leaveGame(){
			$query = $this->db->prepare('UPDATE robots SET used=false WHERE(SELECT games.refrobot FROM GAMES WHERE refmember=?)=robots.id') ;
			$query->execute(array($this->user->id));
			$query = $this->db->prepare('DELETE FROM games WHERE refmember=?') ;
			$query->execute(array($this->user->id));
			$message='Vous avez bien été enlevé de la partie';
			$this->view->message('Partie quittée' , $message, '/play');
	}
	
	public function displayQueue(){
		$this->view->addAdditionalJs('http://spykee.lan/js/queue.js');
		$this->view->addAdditionalCss('http://spykee.lan/css/ui-darkness/jquery-ui-1.10.3.custom.min.css');
		$this->view->addAdditionalJs('http://spykee.lan/js/jquery-ui-1.10.3.custom.min.js');
		$query = $this->db->prepare('SELECT timestamp,pseudo FROM queue INNER JOIN members ON refmember=id ORDER BY timestamp ASC') ;
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		
	
		foreach( $result as $key=>$value ){  //Extraction of pseudo and timestamp from array $result
			$arr5[]=$key+1;
			$arr=$value;
			$arr2[]=$arr['pseudo'];
			$arr3[]=$arr['timestamp'];
		}
		$arr4=array_combine($arr2,$arr5);
		$this->view->assign(array('arr4' => $arr4));
		foreach ($arr4 as $key=>$username){
			$this->view->assign(array('key' => $key));
			$this->view->assign(array('username' => $username));
		}

		print "<table wdith=\"100%\" border=\"1px\">";
		print "<tr>";
		print "<th>Pseudo</th>";
		print "<th>Place</th>";
		print "</tr>";
		foreach ($arr4 as $key=>$username){
			print "<tr>";
			print "<td>$key </td> ";
			print "<td>$username</td>";
			print "</tr>";
		}
		print "</table>";

	}
	
	public function play(){
		require_once(PATH.'libs/spykee-controller/controllerClient.php');
		$this->_spykee = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');
		$this->view->assign('pageTitle', 'Play');
		// TODO mise en place d'une configuration pour le site
		$this->view->addAdditionalJs('http://spykee.lan/js/play.js');
		$this->view->addAdditionalJs('http://spykee.lan/js/jquery-ui-1.10.3.custom.min.js');
		$this->view->addAdditionalCss('http://spykee.lan/css/ui-darkness/jquery-ui-1.10.3.custom.min.css');
	}
	
	/*public function checkInput(){         JAVASCRIPT/AJAX
		$query = $this->db->prepare('SELECT lastinput FROM games WHERE refmember=?');
		$query->execute(array($this->user->id));
		$result =$query->fetch(PDO::FETCH_ASSOC);
		$resultat=$result['lastinput'];
		if ($resultat > $this->config->game->timeout ){
			return false;
		}
		else{
			return true;
		}
	}
	
	public function trueLastInput(){       JAVASCRIPT/AJAX
		$query = $this->db->prepare('UPDATE games SET lastinput=? WHERE refmember =?') ;
		$query->execute(array($this->_input,$this->user->id));
	}
	
	public function lastInput(){           JAVASCRIPT/AJAX
		$this->_input=time();	
	}
	*/
	public function ajax(){
		require_once(PATH.'libs/spykee-controller/controllerClient.php');
		$this->_spykee = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');
	}
	
	public function up(){
		$this->view->assign('content', $this->_spykee->forward()->jsonFormat());
	}
	
	public function down(){
		$this->view->assign('content', $this->_spykee->back()->jsonFormat());
	}
	
	public function left(){
		$this->view->assign('content', $this->_spykee->left()->jsonFormat());
	}
	
	public function right(){
		$this->view->assign('content', $this->_spykee->right()->jsonFormat());
	}
	
	public function holdingUp(){
		$this->view->assign('content', $this->_spykee->holdingForward()->jsonFormat());
	}
	
	public function holdingDown(){
		$this->view->assign('content', $this->_spykee->holdingBack()->jsonFormat());
	}
	
	public function holdingLeft(){
		$this->view->assign('content', $this->_spykee->holdingLeft()->jsonFormat());
	}
	
	public function holdingRight(){
		$this->view->assign('content', $this->_spykee->holdingRight()->jsonFormat());
	}
	
	public function move(){
		$this->view->assign('content', $this->_spykee->move(10,10)->jsonFormat());
	}
	
	public function stop(){
		$this->view->assign('content', $this->_spykee->stop()->jsonFormat());
	}
	
	public function enableVideo(){
		$this->view->assign('content', $this->_spykee->setVideo(TRUE)->jsonFormat());
	}
	
	public function setSpeed($value){
		$this->view->assign('content', $this->_spykee->setSpeed($value)->jsonFormat());
	}
	
	
	public function canPlay(){
		$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.locked = false AND  robots.used= false');
		$query->execute();
		$tab1 =$query->fetch(PDO::FETCH_ASSOC);
		$dispo=$tab1['id'];
		if ($dispo ==null)
			return false;
		else 
			return $dispo;
		/*$sql = 'SELECT robots.id FROM robots WHERE robots.loked = false
					EXCEPT
					SELECT games.refRobot FROM games WHERE games.lastInput <= '.$this->config->game->timeout;
		$query = $this->db->query($sql);*/
		//$response = $query->fetch(PDO::FETCH_ASSOC);
		// Retourne le premier robot dispponible
	}
	
	

	
}

?>
