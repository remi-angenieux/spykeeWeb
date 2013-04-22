<?php
/*
 * Project: Nathan MVC
* File: /controllers/error.php
* Purpose: controller for the URL access errors of the app.
* Author: Nathan Davison
*/

class ErrorController extends BaseController
{
	//add to the parent constructor
	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		//create the model object
		require(PATH.'models/error.php');
		$this->model = new ErrorModel($this->view);
	}

	//bad URL request error
	protected function badURL()
	{
		$this->model->badURL();
	}
}

?>
