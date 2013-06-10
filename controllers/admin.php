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
			$this->model->displayRobot();
			$this->model->displaySelectsRobot();
			$this->model->displaySelectsUser();
			$this->model->displayGames();
			$this->model->displayUser();
		}
		else{
			$this->model->showNotAllowed();
		}
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
	
	protected function takeControl(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->takeControl($_POST['takeControl']);
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