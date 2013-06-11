<?php
class AdminController extends BaseController
{

	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		require(PATH.'models/admin.php');
		$this->model = new AdminModel($this->view);
	}
	
	//default method
	protected function index(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->index();
			$this->model->displayAdminRobots();
			$this->model->displayRobot();
			$this->model->displaySelectsRobot();
			$this->model->displaySelectsUser();
			$this->model->displayGames();
			$this->model->displayUser();
			if(isset($this->urlValues['wellAddRobot']))
				$this->view->littleMessage('Le robot à été ajouté avec succés.');
			if(isset($this->urlValues['wellAddAdmin']))
				$this->view->littleMessage('L\'administrateur été ajouté avec succés.');
			if(isset($this->urlValues['wellBlock']))
				$this->view->littleMessage('Le robot à été bloqué avec succés.');
			if(isset($this->urlValues['wellDeblock']))
				$this->view->littleMessage('Le robot à été débloqué avec succés.');
			if(isset($this->urlValues['wellSetNotUsed']))
				$this->view->littleMessage('Le robot à été retiré de la partie avec succés.');
			if(isset($this->urlValues['wellModName']))
				$this->view->littleMessage('Changement du nom du robot avec succés.');
			if(isset($this->urlValues['wellModIp']))
				$this->view->littleMessage('Changement de l\'addresse IP du robot avec succés.');
			if(isset($this->urlValues['wellModPort']))
				$this->view->littleMessage('Changement du port du robot avec succés.');
			if(isset($this->urlValues['wellDelRobot']))
				$this->view->littleMessage('Le robot à été effacé avec succés.');
			if(isset($this->urlValues['wellDelUser']))
				$this->view->littleMessage('L\'utilisateur à été effacé avec succés.');
		}
		else{
			$this->model->showNotAllowed();
		}
	}
	

	protected function delUser(){
		$this->model->delUser($_POST);
	}
	
	
	protected function listUser(){
		$this->model->displayUser();
	}
	
	protected function block(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->block($_POST['block']);
		}
		
		else{
			$this->model->showNotAllowed();
		}
	}
	protected function deblock(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->deblock($_POST['deblock']);
		}
		else{
			$this->model->showNotAllowed();
		}
		
	}
	
	protected function changePass(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->changePass($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function delRobot(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->delRobot($_POST['delRobot']);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function takeControlAs(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->takeControlAs($_POST['takeControlAs']);
			print_r($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function modifyRobot(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->modifyRobot($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function addAdmin(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->addAdmin($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function setNotUsed(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->setNotUsed($_POST['setNotUsed']);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function addRobot(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->addRobot($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	
	
	
	
}