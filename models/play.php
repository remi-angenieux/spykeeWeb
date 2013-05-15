<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	
	//data passed to the home index view
	public function index(){
		require_once(PATH.'libs/spykee-controller/controllerClient.php');
		$this->_spykee = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');
		$this->view->assign('pageTitle', 'Play');
		// TODO mettre en place d'une configuration pour le site
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
