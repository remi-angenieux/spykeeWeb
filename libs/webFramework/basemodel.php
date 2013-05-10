<<<<<<< HEAD
<?php
/*
 * Project: Nathan MVC
* File: /classes/basemodel.php
* Purpose: abstract class from which models extend.
* Author: Nathan Davison
*/

class BaseModel {

	protected $view;

	//create the base and utility objects available to all models on model creation
	public function __construct($view)
	{
		$this->view = $view;
		$this->commonViewData();
	}

	//establish viewModel data that is required for all views in this method (i.e. the main template)
	protected function commonViewData() {

	}
}

?>
=======
<?php
/*
 * Project: Nathan MVC
* File: /classes/basemodel.php
* Purpose: abstract class from which models extend.
* Author: Nathan Davison
*/

class BaseModel {

	protected $view;

	//create the base and utility objects available to all models on model creation
	public function __construct($view)
	{
		$this->view = $view;
		$this->commonViewData();
	}

	//establish viewModel data that is required for all views in this method (i.e. the main template)
	protected function commonViewData() {

		//e.g. $this->viewModel->set("mainMenu",array("Home" => "/home", "Help" => "/help"));
	}
}

?>
>>>>>>> parent of 52353da... Premier pas de Spykee via le web
