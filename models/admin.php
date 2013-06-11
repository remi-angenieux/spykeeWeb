<?php
class AdminModel extends BaseModel
{

public function index(){

}

public function displayAdminRobots(){
	$query = $this->db->prepare('SELECT name FROM robots
									 EXCEPT
									SELECT name FROM robots INNER JOIN games ON refrobot=robots.id WHERE robots.id=(SELECT refrobot FROM games)') ;
	$query->execute();
	try{
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
		$adminRobots[]=$value;
		}
		$this->view->assign('adminRobots',$adminRobots);
	}
	catch(PDOException $e){
			Error::displayError($e);
	}
}

public function delUser($var){
	$query = $this->db->prepare('DELETE FROM members WHERE id=?') ;
	$query->execute(array($var['id']));
	try{
		$this->view->redirect('admin/?wellDelUser');
		}
	
	catch(PDOException $e){
			Error::displayError($e);
	}
	
}

public function displayUser(){
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
	$query = $this->db->prepare('SELECT id,pseudo,email,refmember FROM members FULL OUTER JOIN admin ON refmember=id') ;
	$query->execute();
	try{
		$array4 = $query->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($array4 as $key=>$value){
			if($array4[$key]['refmember']){
				$array4[$key]['refmember']='OUI';
			}
			else{
				$array4[$key]['refmember']='NON';
			}
		}
		$this->view->assign('array4',$array4);
	}
	
	catch(PDOException $e){
			Error::displayError($e);
	}
}

public function displaySelectsUser(){
	$query = $this->db->prepare('SELECT pseudo FROM members') ;
	$query->execute();
	try{
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
		$array5[]=$value;
		}
	$this->view->assign('array5',$array5);
		}
	
	catch(PDOException $e){
		Error::displayError($e);
	}
}

public function displayRobot(){
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
	$query = $this->db->prepare('SELECT robots.id,name,ctrip,ctrport,locked,refrobot FROM robots FULL OUTER JOIN games ON games.refrobot=robots.id') ;
	$query->execute();
	try{
		$array = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($array as $key=>$value){
			if($array[$key]['refrobot']){
				$array[$key]['refrobot']='OUI';
			}
			else{
				$array[$key]['refrobot']='NON';
			}
			if($array[$key]['locked']){
				$array[$key]['locked']='OUI';
			}
			else{
				$array[$key]['locked']='NON';
			}
		}
	$this->view->assign('array',$array);
	}
		
	catch(PDOException $e){
		Error::displayError($e);
	}
}

public function displayGames(){
	$query = $this->db->prepare('SELECT refmember,pseudo,refrobot,name FROM games INNER JOIN members ON refmember=members.id INNER JOIN robots on refrobot=robots.id') ;
	$query->execute();
	try{
		$array3 = $query->fetchAll(PDO::FETCH_ASSOC);
		$this->view->assign('array3',$array3);
		}
	catch(PDOException $e){
		Error::displayError($e);
	}
	
}


public function displaySelectsRobot(){
	$query = $this->db->prepare('SELECT name FROM robots') ;
	$query->execute();
	try{
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
			$array1[]=$value;
		}
		$this->view->assign('array1',$array1);
		$query = $this->db->prepare('SELECT id FROM robots') ;
		$query->execute();
		$result = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach ($result as $key=>$value){
			$array2[]=$value;
		}
		$this->view->assign('array2',$array2);
	}
	catch(PDOException $e){
		Error::displayError($e);
	}

	
}

public function changePass($var){
	$query = $this->db->prepare('UPDATE members SET password=? WHERE pseudo=?') ;
	$query->execute(array(sha1($var['pass']),$var['pseudo']));
	try{
		$this->view->redirect('admin/?wellChangePass');
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
}

	
public function block($var){
	$this->view->assign(array('pageTitle' => 'Bloquer un Robot'));
	$query = $this->db->prepare('UPDATE robots SET locked=true WHERE name=?') ;
	$query->execute(array($var));
	try{
		$this->view->redirect('admin/?wellBlock');
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
}

public function deblock($var){
	$this->view->assign(array('pageTitle' => 'Débloquer un Robot'));
	$query = $this->db->prepare('UPDATE robots SET locked=false WHERE name=?') ;
	$query->execute(array($var));
	try{
		$this->view->redirect('admin/?wellDeblock');
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
}

public function showNotAllowed(){
	$message="Vous n'êtes pas autorisé à entrer dans cette page";
	$this->view->message('Erreur',$message,'/admin/play/play');
}

public function takeControlAs($var){
	print_r($var);
	$this->view->assign(array('pageTitle' => 'Contôler un Robot'));
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE id=?') ;
	$query->execute(array($var));
	try{
		$value2 = $query->fetch(PDO::FETCH_ASSOC);
		$value3=$value2['id'];
		$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)') ;
		$query->execute(array($this->user->id,$value3,time()));
		try{
			$value2 = $query->fetch(PDO::FETCH_ASSOC);
			$value3=$value2['id'];
			$message="Vous allez êtres redirigé vers la page de jeu";
			$this->view->message('Redirection',$message,'/admin/play/play');
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
	
	
}

public function delRobot($var){
	$this->view->assign(array('pageTitle' => 'Supprimer un robot'));
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.name=? AND used=false ') ;
	$query->execute(array($var));
	try{
		$value2 = $query->fetch(PDO::FETCH_ASSOC);
		$value3=$value2['id'];
		$query = $this->db->prepare('DELETE FROM robots WHERE robots.name=?') ;
		$query->execute(array($var));
		try{
			$this->view->redirect('admin/?wellDelRobot');
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
}

public function setNotUsed($var){
	$this->view->assign(array('pageTitle' => 'Enlever l\'utilisation d\'un robot'));
	$query = $this->db->prepare('UPDATE robots SET used=false WHERE name=?') ;
	$query->execute(array($var));
	try{
		$query = $this->db->prepare('SELECT refmember FROM games WHERE refrobot=(SELECT robots.id from robots INNER JOIN games ON refrobot=robots.id WHERE robots.name=?)') ;
		$query->execute(array($var));
		try{
			$id = $query->fetch(PDO::FETCH_ASSOC);
			$query = $this->db->prepare('DELETE FROM games WHERE refmember=?') ;
			$query->execute(array($id['refmember']));
			try{
				$this->view->redirect('admin/?wellSetNotUsed');
			}
			catch(PDOException $e){
				Error::displayError($e);
			}
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	catch(PDOException $e){
		Error::displayError($e);
	}

	

}

/*public function isIp($string){
	if (preg_match('#^([0-9]{1,3}\.){3}[0-9]{1,3}$#', $string) == 1)
		return true;
	else
		return false;
}

public function isPort($string){
	if (preg_match('/[a-zA-Z]/', $string) == 1)
		return true;
	else
		return false;
}

public function isName($string){
	if ( preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $string)==1 )
        return true;
	else
		return false;
}*/


public function modifyRobot($var){

	if($var['modifyName']){
		$query = $this->db->prepare('UPDATE robots SET name=? WHERE id=? ') ;
		$query->execute(array($var['modifyName'],$var['modify']));
		try{
			$this->view->redirect('admin/?wellModName');
			if($var['modifyCtrip']){
				$query = $this->db->prepare('UPDATE robots SET ctrip=? WHERE id=? ') ;
				$query->execute(array($var['modifyCtrip'],$var['modify']));
				try{
					$this->view->redirect('admin/?wellModIp');
					if($var['modifyCtrport']){
						$query = $this->db->prepare('UPDATE robots SET ctrport=? WHERE id=? ') ;
						$query->execute(array($var['modifyCtrport'],$var['modify']));
						try{
							$this->view->redirect('admin/?wellModPort');
						}
						catch(PDOException $e){
							Error::displayError($e);
						}
					}
				}
				catch(PDOException $e){
					Error::displayError($e);
				}
			}
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
}


public function addRobot($var){
	if($var['addName'] && $var['addCtrip'] && $var['addCtrport'] && $var['addId']){
		$this->view->assign(array('pageTitle' => 'Ajouter un Robot'));
		$query = $this->db->prepare('INSERT INTO robots (id,name,ctrip,ctrport) VALUES (?,?,?,?)') ;
		$query->execute(array($var['addId'],$var['addName'],$var['addCtrip'],$var['addCtrport']));
		try{
			$this->view->redirect('admin/?wellAddRobot');
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	else{
		$message = 'Vous n\'avez pas entré de valeur pour tous les champs.';
					$this->view->littleError($message);
	}
}

public function addAdmin($var){
	$value=$var['addAdmin'];
	$query = $this->db->prepare('SELECT members.id FROM members WHERE members.pseudo=?') ;
	$query->execute(array($value));
	try{
		$value2 = $query->fetch(PDO::FETCH_ASSOC);
		$value3=$value2['id'];
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
	$query = $this->db->prepare('INSERT INTO admin refmember) VALUES (?');
	$query->execute(array($value3));
	try{
		$this->view->redirect('admin/?wellAddAdmin');
	}
	catch(PDOException $e){
		Error::displayError($e);
	}
}
}