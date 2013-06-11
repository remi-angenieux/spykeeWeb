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
		$this->view->setEnvironement('home');
		$this->view->assign(array('pageTitle' => 'Accueil',
								'username' => $this->user->pseudo));
	}
	
	public function displaySelectsRobot(){
		$query = $this->db->prepare('SELECT name FROM robots') ;
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
			$array1[]=$value;
		}
		$this->view->assign('array1',$array1);
	
	}
	
}



?>
