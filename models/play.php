<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	
	//data passed to the home index view
	public function index(){
		$this->view->assign('pageTitle', 'Jouer');
		$this->view->addAdditionalJs('jquery-ui-1.10.3.custom.min.js');
		$this->view->addAdditionalJs('play.js');
		//$this->view->addAdditionalCss('ui-darkness/jquery-ui-1.10.3.custom.min.css');
		$this->view->addAdditionalCss('robot.css');
	}
	
	public function ajax(){
		require_once(PATH.'libs/spykee/spykee-controller/controllerClient.php');
		try{
			$this->_spykee = new SpykeeControllerClient('127.0.0.1', '2000');
		}
		catch (SpykeeException $e){
			require_once PATH.'libs/spykee/response.php';
			$response = new SpykeeResponse(SpykeeControllerClient::STATE_ERROR, SpykeeResponse::UNABLE_TO_CONNECT_TO_CONTROLLER);
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
