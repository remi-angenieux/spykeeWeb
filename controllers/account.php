<?php
class AccountController extends BaseController
{
	//add to the parent constructor
	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		//create the model object
		require(PATH.'models/account.php');
		$this->model = new AccountModel($this->view);
	}

	//default method
	protected function index()
	{
		$this->model->index();
		if(isset($this->urlValues['wellChangePass']))
			$this->view->littleMessage('Votre mot de passe à bien été changé.');
		if(isset($this->urlValues['wellChangeEmail']))
			$this->view->littleMessage('Votre e-mail à bien été changé.');
		if(isset($this->urlValues['wellChangeImg']))
			$this->view->littleMessage('Votre avatar à bien été changé.');
		if(isset($this->urlValues['wellDelHistory']))
			$this->view->littleMessage('Historique des parties effacés.');
		
			
		if ($this->model->isConnected()){
			$this->model->displayProfil();
			$this->model->displayImg();
			$this->model->displayHistory();
			$this->model->displayUser();
			$this->view->setTemplate('profil');
		}
		else{
			$this->model->showLogin();
			$this->view->setTemplate('login');
		}
	}
	
	protected function register(){
		if ($this->model->isConnected())
			$this->model->messageAlreadyConnected();
		else{
			if (empty($_POST['submit'])){
				$this->model->showRegister();
			}
			else{
				$this->model->processRegister($_POST);
			}
		}
	}
	
	protected function delHistory(){
		$this->model->delHistory();
	}
	
	protected function login(){
		if ($this->model->isConnected())
			$this->model->messageAlreadyConnected();
		else{
			if (empty($_POST['submit']))
				$this->model->showLogin();
			else
				$this->model->processLogin($_POST);
		}
	}
	
	protected function logout(){
		if ($this->model->isConnected())
			$this->model->logout();
		else
			$this->model->messageAlreadyLogout();
	}
	
	
	protected function changePass(){
		if ($this->model->isConnected()){
			$this->model->changePass($_POST);
		}
		else{
			$this->model->showNotConnected();
		}
	}
		
	protected function changeMail(){
		if ($this->model->isConnected()){
			$this->model->changeMail($_POST);
		}
		else{
			$this->model->showNotConnected();
		}
	}
	protected function visitProfil(){
	$this->model->visitProfil($_POST);
	
	}

	
	protected function displayHistory(){
		if ($this->model->isConnected()){
			$this->model->displayHistory();
		}
		else{
			$this->model->showNotConnected();
		}
	}
	
	
	protected function uploadImg(){
		$this->model->uploadImg($_FILES,$_POST);
	}
		
		}
