<?php
/*
 * Project: Nathan MVC
* File: /classes/basemodel.php
* Purpose: abstract class from which models extend.
* Author: Nathan Davison
*/

class BaseModel {

	protected $view;
	protected $db;
	protected $config;
	protected $user;

	//create the base and utility objects available to all models on model creation
	public function __construct($view)
	{
		$this->view = $view;
		$this->commonViewData();
		$this->config = Config::getInstance();
		$this->db = Db::getInstance()->db();
		$this->user = User::getInstance();
		
	}

	public function isConnected(){
		if ($this->user->id != $this->config->global->guestId AND $this->user->id != $this->config->global->banId)
			return true;
		else
			return false;
	}
	
	public function isAdmin(){
		if ($this->user->refmember==$this->user->id){
			return true;
		}
		else{
			return false;
		}
	}

	
	//establish viewModel data that is required for all views in this method (i.e. the main template)
	protected function commonViewData() {

	}
	
}

?>
