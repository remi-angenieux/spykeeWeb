<?php
class AdminModel extends BaseModel
{

public function index(){
	$this->view->addAdditionalCss('admin.css');
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
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

	$query = $this->db->prepare('SELECT id,pseudo,email,refmember FROM members FULL OUTER JOIN admin ON refmember=id');
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
	$query = $this->db->prepare('SELECT pseudo FROM members 
								EXCEPT SELECT pseudo FROM members WHERE id=-1 OR id=0 ORDER BY pseudo ASC ') ;
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
	$query = $this->db->prepare('SELECT robots.id,name,ctrip,ctrport,locked,refrobot FROM robots FULL OUTER JOIN games ON games.refrobot=robots.id ORDER BY robots.id ASC') ;
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
	$query = $this->db->prepare('SELECT name FROM robots ORDER BY name ASC') ;
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
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE name=?') ;
	$query->execute(array($var));
	try{
		$value2 = $query->fetch(PDO::FETCH_ASSOC);
		$value3=$value2['id'];
		$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)') ;
		$query->execute(array($this->user->id,$value3,time()));
		try{
			$message="Vous allez êtres redirigé vers la page de jeu";
			$this->view->message('Redirection',$message,'play/play');
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
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.name=?') ;
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
	$query = $this->db->prepare('DELETE FROM games WHERE refrobot=(SELECT robots.id FROM robots WHERE robots.name=?)') ;
	$query->execute(array($var));
	try{
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
	$bool=true;
	$this->view->setTemplate('index');
	
	if($var['modifyName']){
		$query = $this->db->prepare('SELECT name FROM robots') ;
		$query->execute();
		try{
			$resultat= $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($resultat as $value){
				$resultat2[]=$value['name'];
			}
			foreach($resultat2 as $value2){
				if($var['modifyName']==$value2){
					$bool=false;
					break;
				}
		}
		if($bool==true){
			$query = $this->db->prepare('UPDATE robots SET name=? WHERE id=? ') ;
			$query->execute(array($var['modifyName'],$var['modify']));
			try{
				$this->view->redirect('admin/?wellModName');
			}
			catch(PDOException $e){
				Error::displayError($e);
			}
		}
		else{
			$this->view->redirect('admin/?badModName');
		}
	}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	

	
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
		if($var['modifyCtrip']){
			if (filter_var($var['modifyCtrip'], FILTER_VALIDATE_IP)){
				$bool2=true;
				$query = $this->db->prepare('SELECT ctrip FROM robots') ;
				$query->execute();
				try{
					$resultat= $query->fetchAll(PDO::FETCH_ASSOC);
					foreach($resultat as $value){
						$resultat2[]=$value['ctrip'];
					}
					foreach($resultat2 as $value2){
						if($var['modifyCtrip']==$value2){
							$bool2=false;
							break;
						}
					}
					if($bool2==true){
						$query = $this->db->prepare('UPDATE robots SET ctrip=? WHERE id=? ') ;
						$query->execute(array($var['modifyCtrip'],$var['modify']));
						try{
							$this->view->redirect('admin/?wellModIp');
						}
						catch(PDOException $e){
							Error::displayError($e);
						}
					}
					else{
						$this->view->redirect('admin/?badModIpSame');
					}
				}
				catch(PDOException $e){
					Error::displayError($e);
				}
			}
			else{
				$this->view->redirect('admin/?badModIp');
			}
		}
	}
	

public function addRobot($var){
	
	$this->view->setTemplate('index');
	$var1=3;
	if (filter_var($var['addCtrip'], FILTER_VALIDATE_IP)){ //Test if ip is valid
		$query = $this->db->prepare('SELECT name,ctrip,id FROM robots') ;
		$query->execute();
		try{
			$resultat= $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($resultat as $value){
				$resultat2[]=$value['ctrip'];
				$resultat3[]=$value['name'];
				$resultat4[]=$value['id'];
			}
			foreach($resultat2 as $value2){   //Test if ip already exits
				if($var['addCtrip']==$value2 ){
					$var1=0;
					break;
				}
			}
			foreach($resultat3 as $value3){		//Test if name already exits
				if($var['addName']==$value3 ){
					$var1=1;
					break;
				}
			}
			foreach($resultat4 as $value4){		//Test if id already exits
				if($var['addId']==$value4 ){
					$var1=2;
					break;
				}
			}
			if($var1==3){ 						 //Then all works fine
				$query = $this->db->prepare('INSERT INTO robots (id,name,ctrip,ctrport) VALUES (?,?,?,?)') ;
				$query->execute(array($var['addId'],$var['addName'],$var['addCtrip'],$var['addCtrport']));
				try{
					$this->view->redirect('admin/?wellAddRobot');
				}
				catch(PDOException $e){
					Error::displayError($e);
				}
			}
			else if($var1==0){
				$this->view->redirect('admin/?badModIpSame');
			}
			else if($var1==1){
				$this->view->redirect('admin/?badModName');
			}
			else{
				$this->view->redirect('admin/?badModIdSame');
			}
		}
		catch(PDOException $e){
			Error::displayError($e);
		}
	}
	else{
		$this->view->redirect('admin/?badModIp');
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