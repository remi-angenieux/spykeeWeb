<?php

class PlayModel extends BaseModel
{
	protected $_spykee;
	
	//data passed to the home index view
	public function index(){
		require_once(PATH.'libs/spykee-controller/controllerClient.php');
		$this->_spykee = new SpykeeControllerClient('Robot1', '127.0.0.1', '2000');
		$this->view->assign('pageTitle', 'Play');
	}
	
	public function up(){
		$this->_spykee->forward();
	}
	
	public function left(){
		$this->_spykee->turnLeft();
	}
	
	public function right(){
		$this->_spykee->turnRight();
	}
	
	public function down(){
		$this->_spykee->back();
	}
}

?>
