<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	//public $_input;

	//data passed to the home index view
	public function index(){
		$this->view->assign(array('pageTitle' => 'Play'));
	}
	
	public function showNotConnected(){
		$this->view->assign(array('pageTitle' => 'Erreur'));
		$message = 'Vous devez être connecté pour pouvoir jouer';
		$this->view->message('Vous n\' êtes pas connecté' , $message, 'account/login');
	}
	

	public function showNotAllowed(){
		$this->view->assign(array('pageTitle' => 'Erreur'));
		$message='Vous n\'êtes pas autorisé a jouer';
		$this->view->message('Erreur' , $message, '/play');
	}
	
	public function displayImg(){
		//TODO mettre le dossier d'image a image/profil
		$imgDir=$this->config->global->rootUrl."images/";
		$query = $this->db->prepare('SELECT image FROM members INNER JOIN games ON games.refmember=members.id') ;
		$query->execute();
		$array = $query->fetch(PDO::FETCH_ASSOC);
		$resultat=$array['image'];
		if(!$resultat){
			$src=$imgDir.'default.jpg';
		}
		else{
			$src=$imgDir.$resultat;
		}
		$this->view->assign('src',$src);
	}
	
	//add member to queue
	public function enterQueue(){
		print "<div id=\"queue\">";
		$arr2=array();
		$arr3=array();
		$query = $this->db->prepare('INSERT INTO queue (refmember,timestamp) VALUES(?,?)') ;
		$query->execute(array($this->user->id,time()));
	}
	
	public function enterGameAdmin(){
		$dispo=$this->canPlayAdmin();
		$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?');
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)');
		$query->execute(array($this->user->id, $dispo,time()));
		$query = $this->db->prepare('UPDATE robots SET used=true WHERE robots.id=? ');
		$query->execute(array($dispo));
		$query = $this->db->prepare('INSERT INTO gameshistory (refmember,refrobot,date,duration) VALUES(?,?,?,?)') ;
		$query->execute(array($this->user->id,$dispo,date('c'),time()));
	}
	
	public function enterGame(){
		$dispo=$this->canPlay();
		$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?');
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)') ;
		$query->execute(array($this->user->id,$dispo,time()));
		$query = $this->db->prepare('UPDATE robots SET used=true WHERE robots.id=? ') ;
		$query->execute(array($dispo));
		$query = $this->db->prepare('INSERT INTO gameshistory (refmember,refrobot,date,duration) VALUES(?,?,?,?)') ;
		$query->execute(array($this->user->id,$dispo,date('c'),time()));
	}
	
	public function leaveGame(){
		$this->view->assign(array('pageTitle' => 'Partie quittée'));
		$query = $this->db->prepare('UPDATE robots SET used=false WHERE(SELECT games.refrobot FROM games WHERE refmember=?)=robots.id') ;
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('DELETE FROM games WHERE refmember=?') ;
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('SELECT duration FROM gameshistory WHERE refmember=? AND date=(SELECT MAX(date) FROM gameshistory)');
		$query->execute(array($this->user->id));
		$result =$query->fetch(PDO::FETCH_ASSOC);
		$duration=time()-$result['duration'];
		$query = $this->db->prepare('UPDATE gameshistory SET duration=? WHERE date=(SELECT MAX(date) FROM gameshistory) ') ;
		$query->execute(array($duration));
		$message='Vous avez bien été enlevé de la partie';
		$this->view->message('Partie quittée' , $message, '/play');
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
		if (!$resultat){
			return false;
		}
		else{
			return true;
		}
	}//Check if the current user is in the game 
	

	public function isFirst(){
		//TODO essayer de fusionner les requêtes
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
			$this->view->assign(array('pageTitle' => 'File quittée'));
			$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?') ;
			$query->execute(array($this->user->id));
			$message='Vous avez bien été enlevé de la file';
			$this->view->message('File quittée' , $message, '/play');
	}
	
	
	public function displayQueue(){
		$this->view->assign(array('pageTitle' => 'File d\'attente'));
		$this->view->addAdditionalJs('queue.js');
		try{
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
		}
		catch(PDOException $e){
			Error::diplayError($e);
			$arr4='';
		}
		$this->view->assign('arr4',$arr4);
	}
	
	public function play(){
		$this->view->assign('pageTitle', 'Jouer');
		$this->view->addAdditionalJs('jquery-ui-1.10.3.custom.min.js');
		$this->view->addAdditionalJs('play.js');
		$this->view->addAdditionalCss('robot.css');
	}
	
	public function canPlay(){
		//TODO enlever le champs used et passer par inner join
		$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.locked = false AND robots.used= false');
		$query->execute();
		$tab1 =$query->fetch(PDO::FETCH_ASSOC);
		$dispo=$tab1['id'];
		if (!$dispo)
			return false;
		else
			return $dispo;
	}
	
	public function canPlayAdmin(){
		//TODO l'admin doit pouvoir choisir son robot
		$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.used= false');
		$query->execute();
		$tab1 =$query->fetch(PDO::FETCH_ASSOC);
		$dispo=$tab1['id'];
		if (!$dispo)
			return false;
		else
			return $dispo;
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
		require_once(PATH.'libs/spykee/spykee-controller/controllerClient.php');
		try{
			$query = $this->db->prepare('SELECT name,ctrip,ctrport FROM robots INNER JOIN games ON robots.id=games.refrobot WHERE refmember=? ') ;
			$query->execute(array($this->user->id));
			$result = $query->fetch(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			require_once PATH.'libs/spykee/response.php';
			$response = new SpykeeResponse(SpykeeControllerClient::STATE_ERROR, SpykeeResponse::UNABLE_TO_CONNECT_TO_CONTROLLER);
			$this->view->assign('content', $response->jsonFormat());
			return FALSE;
		}
		try{
			$this->_spykee = new SpykeeControllerClient($result['ctrip'], $result['ctrport']);
		}
		catch (SpykeeException $e){
			
			if (!$this->user->isAdmin){
				require_once PATH.'libs/spykee/response.php';
				$response = new SpykeeResponse(SpykeeControllerClient::STATE_ERROR, SpykeeResponse::UNABLE_TO_CONNECT_TO_CONTROLLER);
				$json = $response->jsonFormat();
			}
			else
				$json = '{"state": 0, data: "", "description: "'.$e->getMessage().', idDescription: 0"}';
			$this->view->assign('content', $response->jsonFormat());
			return FALSE;
		}
		return TRUE;
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
	
	public function getSpeed(){
		$this->view->assign('content', $this->_spykee->getSpeed()->jsonFormat());
	}
	
}

?>
