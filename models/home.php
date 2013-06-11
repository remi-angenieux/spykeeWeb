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
	
	public function displayAdminRobots(){
		$query = $this->db->prepare('SELECT name FROM robots
									 EXCEPT 
									SELECT name FROM robots INNER JOIN games ON refrobot=robots.id WHERE robots.id=(SELECT refrobot FROM games)') ;
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
			$adminRobots[]=$value;
		}
		$this->view->assign('adminRobots',$adminRobots);
	
	}
	
}



?>
