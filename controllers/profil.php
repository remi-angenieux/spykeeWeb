<?php
class ProfilController extends BaseController
{

	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		require(PATH.'models/profil.php');
		$this->model = new ProfilModel($this->view);
	}
	
	public function index(){
		if ($this->model->isConnected()){
			$this->model->displayProfil();
			$this->model->displayImg();
			$this->model->history();
		}
		else{
			$this->model->showNotConnected();
		}
		
	
	}
	
		
	
	public function changePass(){
		if ($this->model->isConnected()){
			$this->model->changePass($_POST);
		}
		else{
			$this->model->showNotConnected();
		}
	}
	
	public function displayHistory(){
		if ($this->model->isConnected()){
			$this->model->displayHistory();
		}
		else{
			$this->model->showNotConnected();
		}
	}
	
	
	public function uploadImg(){
		$this->model->uploadImg($_FILES,$_POST);
	}
	
	
	
	}