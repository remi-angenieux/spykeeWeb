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
}

?>
