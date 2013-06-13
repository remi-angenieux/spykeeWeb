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
			$this->model->displayQueue();
			$this->model->displayMemberInQueue();
			if(isset($this->urlValues['wellAddRobot']))
				$this->view->littleMessage('Le robot à été ajouté avec succés.');
			if(isset($this->urlValues['wellAddAdmin']))
				$this->view->littleMessage('L\'administrateur été ajouté avec succés.');
			if(isset($this->urlValues['wellDelAdmin']))
				$this->view->littleMessage('L\'administrateur été enlevé avec succés.');
			if(isset($this->urlValues['wellBlock']))
				$this->view->littleMessage('Le robot à été bloqué avec succés.');
			if(isset($this->urlValues['wellChangePass']))
				$this->view->littleMessage('Le mot de passe à été modifié avec succés.');
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
			if(isset($this->urlValues['badModName']))
				$this->view->littleError('Ce nom existe déjà.');
			if(isset($this->urlValues['badModIpSame']))
				$this->view->littleError('Cette addresse IP existe déjà.');
			if(isset($this->urlValues['badModIp']))
				$this->view->littleError('L\'addresse IP rentrée n\'est pas valide.');
			if(isset($this->urlValues['badModIdSame']))
				$this->view->littleError('L\'id rentré existe déjà.');
			if(isset($this->urlValues['wellPutOutOfQueue']))
				$this->view->littleMessage('L\'utilisateur à bien été enlever de la file.');
		}
		else{
			$this->model->showNotAllowed();
		}
	}
	
	protected function delAdmin(){
		$this->model->delAdmin($_POST);
	}
	protected function delUser(){
		$this->model->delUser($_POST);
	}
	protected function putOutOfQueue(){
		$this->model->putOutOfQueue($_POST);
	}
	
	protected function listUser(){
		$this->view->addAdditionalCss('queue.css');
		$this->model->displayUser();
	}
	
	protected function block(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->block($_POST);
		}
		
		else{
			$this->model->showNotAllowed();
		}
	}
	protected function deblock(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->deblock($_POST);
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
			$this->model->delRobot($_POST);
		}
		else{
			$this->model->showNotAllowed();
		}
	
	}
	
	protected function takeControlAs(){
		if($this->model->isAdmin() && $this->model->isConnected()){
			$this->model->takeControlAs($_POST);
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
			$this->model->setNotUsed($_POST);
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