<?php
class PlayController extends BaseController
{
	//add to the parent constructor
	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		//create the model object
		require(PATH.'models/play.php');
		$this->model = new PlayModel($this->view);
	}

	//default method
	protected function index()
	{
		if ($this->model->isConnected()){
			//TODO si il est 1er => redirigé sur /play
			$this->model->index();
			$this->model->isInQueue();
		}
		else{
			$this->model->notConnected();
		}
		/*if (!empty($_POST['up']))
			$this->model->up();
		if (!empty($_POST['left']))
			$this->model->left();
		if (!empty($_POST['right']))
			$this->model->right();
		if (!empty($_POST['down']))
			$this->model->down();
		
		if (!empty($_POST['holdingUp']))
			$this->model->holdingUp();
		if (!empty($_POST['holdingLeft']))
			$this->model->holdingLeft();
		if (!empty($_POST['holdingRight']))
			$this->model->holdingRight();
		if (!empty($_POST['holdingDown']))
			$this->model->holdingDown();
		
		if (!empty($_POST['move']))
			$this->model->move();
		if (!empty($_POST['stop']))
			$this->model->stop();
		if (!empty($_POST['enableVideo']))
			$this->model->enableVideo();*/
	}
	
	protected function queue(){
	if ($this->model->isConnected()){
		$this->model->inQueue();
	}
	else{
		$this->model->notConnected();
	}
	}
	
	protected function play(){
		$freeRobot=2;
		//$this->model->Canplay();

		if($freeRobot AND $this->model->isFirst()){//Check if a robot is available and if the user is the 1st of the queue	
		$this->model->enterGame();	
		$this->model->play();
		}
		else{
			$this->model->notAllowed();
		}
	}
	
	
	protected function ajax(){
		$this->model->ajax();
		$this->view->setTextPage();
		if (!empty($_POST['action'])){
			switch ($_POST['action']){
				case 'up':
					$this->model->up();
					break;
				case 'down':
					$this->model->down();
					break;
				case 'left':
					$this->model->left();
					break;
				case 'right':
					$this->model->right();
					break;
					
				case 'holdingUp':
					$this->model->holdingUp();
					break;
				case 'holdingDown':
					$this->model->holdingDown();
					break;
				case 'holdingLeft':
					$this->model->holdingLeft();
					break;
				case 'holdingRight':
					$this->model->holdingRight();
					break;
					
				case 'move':
					$this->model->move();
					break;
				case 'stop':
					$this->model->stop();
					break;
				case 'enableVideo':
					$this->model->enableVideo();
					break;
				case 'setSpeed':
					$this->model->setSpeed($_POST['data']);
					break;			
			}
		}
	}
	

	}
		

?>