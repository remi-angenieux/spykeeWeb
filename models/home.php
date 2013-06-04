<?php
/*
 * Project: Nathan MVC
* File: /models/home.php
* Purpose: model for the home controller.
* Author: Nathan Davison
*/

class HomeModel extends BaseModel
{
	//data passed to the home index view
	public function index()
	{
		$this->view->setEnvironement('header');
		$this->view->assign(array('pageTitle' => 'Accueil',
								'username' => $this->user->pseudo));
	}
}

?>
