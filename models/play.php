<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	//public $_input;

	//data passed to the home index view
	public function index(){
		
	}
	

	public function displayAdminRobots(){
		$query = $this->db->prepare('SELECT name FROM robots
									 EXCEPT
									SELECT name FROM robots INNER JOIN games ON refrobot=robots.id WHERE robots.id=(SELECT refrobot FROM games)') ;
		$query->execute();
		try{
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
			$adminRobots[]=$value;
		}
		$this->view->assign('adminRobots',$adminRobots);
		}
			catch (PDOException $e){
				if($this->model->isAdmin){
					Error::displayError($e);
				}
	       }
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

	//add member to queue
	public function enterQueue(){
		$query = $this->db->prepare('INSERT INTO queue (refmember,timestamp) VALUES(?,?)') ;
		$query->execute(array($this->user->id,time()));
		try{
		}
		catch (PDOException $e){
			if($this->model->isAdmin){
				Error::displayError($e);
				$this->view->redirect('?badEnterQueue');
			}
		}
	}
	
	public function isRobotOn(){
		//TODO faire une fonction qui check si un robot est en fonction
	}


		public function enterGame(){
		$dispo=$this->canPlay();
		$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?');
		$query->execute(array($this->user->id));
		try{
			$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)');
			$query->execute(array($this->user->id, $dispo,time()));
			try{
				$query = $this->db->prepare('INSERT INTO gameshistory (refmember,refrobot,date,duration) VALUES(?,?,?,?)') ;
				$query->execute(array($this->user->id,$dispo,date('c'),time()));
				try{
				}
				catch (PDOException $e){
					if($this->model->isAdmin)
						Error::displayError($e);
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
					Error::displayError($e);
					$this->view->setTemplate('index.tpl');
					$this->view->redirect('play?badEnterGame');
			}
		}
		catch (PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			$this->view->setTemplate('index.tpl');
			$this->view->redirect('play?badLeaveQueue');
		}
		
		
	}
		
	
	
	public function leaveGame(){
		$this->view->assign(array('pageTitle' => 'Partie quittée'));
		$query = $this->db->prepare('DELETE FROM games WHERE refmember=?') ;
		$query->execute(array($this->user->id));
		try{
			$query = $this->db->prepare('SELECT duration FROM gameshistory WHERE refmember=? AND date=(SELECT MAX(date) FROM gameshistory)');
			$query->execute(array($this->user->id));
			try{
				$result =$query->fetch(PDO::FETCH_ASSOC);
				$duration=time()-$result['duration'];
				$query = $this->db->prepare('UPDATE gameshistory SET duration=? WHERE date=(SELECT MAX(date) FROM gameshistory) ') ;
				$query->execute(array($duration));
				try{
					$this->view->setTemplate('index.tpl');
					$this->view->redirect('play?wellLeaveGame');
				}
				catch (PDOException $e){
					if($this->model->isAdmin)
					Error::displayError($e);
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
				Error::displayError($e);
			}
		}
		catch (PDOException $e){
			if($this->model->isAdmin)
			Error::displayError($e);
			$this->view->setTemplate('play.tpl');
			$this->view->redirect('play/play?badLeaveGame');
		}
		
		
		
	}
	
	

	public function isInQueue(){                                    
			$query = $this->db->prepare('SELECT refmember FROM queue WHERE refmember=?');
			$query->execute(array($this->user->id));
			try{
				$result =$query->fetch(PDO::FETCH_ASSOC);
				$resultat=$result['refmember'];
				if ($resultat==null){
					return false;
				}
				else{
					return true;
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
				Error::displayError($e);
			}
	} //Check if the current user is in the queue 
	

	public function isInGame(){
		$query = $this->db->prepare('SELECT refmember FROM games WHERE refmember=?');
		$query->execute(array($this->user->id));
		try{
			$result = $query->fetch(PDO::FETCH_ASSOC);
			$resultat=$result['refmember'];
			if (!$resultat){
				return false;
			}
			else{
				return true;
			}
		}
		catch (PDOException $e){
			if($this->model->isAdmin)
			Error::displayError($e);
		}
		
	}//Check if the current user is in the game 
	

	public function isFirst(){

			$query = $this->db->prepare('SELECT refmember FROM queue WHERE timestamp =(SELECT MIN(timestamp) FROM queue)');
			$query->execute();
			try{
				$result =$query->fetch(PDO::FETCH_ASSOC);
				$resultat=$result['refmember'];
				if ($this->user->id==$resultat){
					return true;
				}
				else{
					return false;
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
				Error::displayError($e);
			}
			
		}
	 //Check if the current user is the 1st of the queue


	public function leaveQueue(){
			$this->view->assign(array('pageTitle' => 'File quittée'));
			$query = $this->db->prepare('DELETE FROM queue WHERE refmember=?') ;
			$query->execute(array($this->user->id));
			try{
				$this->view->setTemplate('play.tpl');
				$this->view->redirect('play?wellLeaveQueue');
				}
				catch (PDOException $e){
					if($this->model->isAdmin)
					Error::displayError($e);
			}
	}
	
	
	public function displayQueue(){
		$imgDir=$this->config->global->rootUrl."images/profils/";
		$query = $this->db->prepare('SELECT image FROM members INNER JOIN games ON games.refmember=members.id') ;
		$query->execute();
		try{
			$array = $query->fetch(PDO::FETCH_ASSOC);
			$resultat=$array['image'];
			if(!$resultat){
				$src=$imgDir.'blanc.jpg';
			}
			else{
				$src=$imgDir.$resultat;
			}
			$this->view->assign('src',$src);
		}
		catch (PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
		}
		
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
		$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.locked = false EXCEPT SELECT games.refrobot FROM games');
		$query->execute();
		try{
			$tab1 =$query->fetch(PDO::FETCH_ASSOC);
			$dispo=$tab1['id'];
			if (!$dispo)
				return false;
			else
				return $dispo;
			}
		catch (PDOException $e){
			if($this->model->isAdmin)
			Error::displayError($e);
		}
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
