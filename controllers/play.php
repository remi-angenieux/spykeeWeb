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
		$this->model->displayAdminRobots();
		if ($this->model->isConnected()){
			$this->model->index();
			if($this->model->isInQueue()){
				$this->model->leaveQueue();
			}
			if($this->model->isInGame()){
				$this->model->leaveGame();
			}
			
		}
		else{
			$this->model->showNotConnected();
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
		//TODO afficher message pas de robot dispo
	if ($this->model->isInQueue()){
    	$this->model->displayQueue();
    	$this->model->displayImg();
	}	
	else{
		if ($this->model->isConnected()){
			$this->model->enterQueue();
			$this->model->displayQueue();
			$this->model->displayImg();
		}
		else{
			$this->model->showNotConnected();
		}
	}
	if($this->model->isAdmin()){
		if($this->model->isFirst() && $this->model->canPlayAdmin()){
			$this->view->addAdditionalJs('askplay.js');
		}
	}
	else{
		if($this->model->isFirst() && $this->model->canPlay()){
			$this->view->addAdditionalJs('askplay.js');
		}
	}

	}
	protected function play(){
		
		if($this->model->isInGame()){
			if(!$this->model->isAdmin())
			$this->model->leaveGame();
			else{
			$this->model->play();
			}
		}
		else if($this->model->isAdmin())
		{
			$this->model->enterGameAdmin();
			$this->model->play();
		}
		else {
			if(!$this->model->canPlay()){
				$this->model->showNotAllowed();
			}
			else{
				if($this->model->isFirst()){//if the user is the 1st of the queue
					if(!$this->model->isInGame())
						$this->model->enterGame();
					$this->model->play();
				}
				else{
					$this->model->showNotAllowed();
				}
			}
		}
		
	}
	
	
	protected function ajax(){
		$this->view->setEnvironement('empty');
		if ($this->model->ajax()){
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
					case 'getSpeed':
						$this->model->getSpeed();
					break;
				}
			}
		}
	}
	

	}
		

?>
