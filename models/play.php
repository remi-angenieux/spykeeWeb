<?php

class PlayModel extends BaseModel
{
	protected $_spykee;

	//data passed to the home index view
	public function index(){

		//TODO si il est 1er => redirigé sur /play/play
	}
	
	public function notConnected(){
		$message = 'Vous devez être connecté pour pouvoir jouer';
		$this->view->message('Vous n\' êtes pas connecté' , $message, '/account/login');
	}
	//add member to queue
	public function inQueue(){
	
		$arr2=array();
		$arr3=array();
		$query = $this->db->prepare('INSERT INTO queue (refmember,timestamp) VALUES(?,?)') ;
		$query->execute(array($this->user->id,time()));
	
		$query = $this->db->prepare('SELECT timestamp,pseudo FROM queue INNER JOIN members ON refmember=id ORDER BY timestamp ASC;') ;
		$result=$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);

		foreach( $result as $key=>$value ){  //Extraction of pseudo and timestamp from array $result 
		 $arr5[]=$key+1;
		 $arr=$value;
		 $arr2[]=$arr['pseudo']; 
		 $arr3[]=$arr['timestamp'];
		}
		$arr4=array_combine($arr2,$arr5);   
		
		print "<table wdith=\"100%\" border=\"5px\">";
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
	


	public function displayQueue(){
		
		
	}
	//delete member from queue
	public function isInQueue(){                                        
			$query = $this->db->prepare('SELECT refmember FROM queue WHERE refmember=?');
			$query->execute(array($this->user->id));
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			if (count($result) >= 1){
				$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?') ;
				$query->execute(array($this->user->id));
				$message='Vous avez bien été enlevé de la file';
				$this->view->message('File quittée' , $message, '/play');
			
		    }
	}
	
	/*public function inGame(){
		//TODO Si l'user est 1er de la queue alors :
		$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?') ;
		$query->execute(array($this->user->id));
		$query = $this->db->prepare('INSERT INTO games (id,refmember,refrobot,starttime,lastinput) VALUES(?,?,?,?,?)') ;
		$query->execute(array(1,$this->user->id,,time()1));
	}*/
	

	
	
	public function play(){
		require_once(PATH.'libs/spykee-controller/controllerClient.php');
		$this->_spykee = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');
		$this->view->assign('pageTitle', 'Play');
		// TODO mise en place d'une configuration pour le site
		$this->view->addAdditionalJs('http://spykee.lan/js/play.js');
		$this->view->addAdditionalJs('http://spykee.lan/js/jquery-ui-1.10.3.custom.min.js');
		$this->view->addAdditionalCss('http://spykee.lan/css/ui-darkness/jquery-ui-1.10.3.custom.min.css');
	}
	
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
		$sql = 'SELECT robots.id FROM robots WHERE robots.loked = false
					EXCEPT
					SELECT games.refRobot FROM games WHERE games.lastInput <= '.$this->config->game->timeout;
		$query = $this->db->query($sql);
		$response = $query->fetch(PDO::FETCH_ASSOC);
		// Retourne le premier robot dispponible
	}
	
	

	
}

?>
