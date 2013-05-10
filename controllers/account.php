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
		if ($this->model->isConnected()){
			$this->model->showProfile();
			$this->view->setTemplate('profile');
		}
		else{
			$this->model->login();
			$this->view->setTemplate('connection');
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
}