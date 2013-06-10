<?php
class AdminModel extends BaseModel
{

public function index(){
	
	
}

public function displayUser(){
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
	$query = $this->db->prepare('SELECT id,pseudo,email,refmember FROM members FULL OUTER JOIN admin ON refmember=id') ;
	$query->execute();
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

public function displaySelectsUser(){
	$query = $this->db->prepare('SELECT pseudo FROM members') ;
	$query->execute();
	$result = $query->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result as $key=>$value){
		$array5[]=$value;
	}
	$this->view->assign('array5',$array5);



}

public function displayRobot(){
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
	$query = $this->db->prepare('SELECT * FROM robots') ;
	$query->execute();
	$array = $query->fetchAll(PDO::FETCH_ASSOC);
	$this->view->assign('array',$array);
	
}

public function displayGames(){
	$query = $this->db->prepare('SELECT refmember,pseudo,refrobot,name FROM games INNER JOIN members ON refmember=members.id INNER JOIN robots on refrobot=robots.id') ;
	$query->execute();
	$array3 = $query->fetchAll(PDO::FETCH_ASSOC);
	$this->view->assign('array3',$array3);

}


public function displaySelectsRobot(){
	$query = $this->db->prepare('SELECT name FROM robots') ;
	$query->execute();
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

public function changePass($var){
	$query = $this->db->prepare('UPDATE members SET password=? WHERE pseudo=?') ;
	$query->execute(array(sha1($var['pass']),$var['pseudo']));
	$message='Mot de passe changé avec succés';
	$this->view->message('Succés' , $message, '/admin');
}

	
public function block($var){
	$this->view->assign(array('pageTitle' => 'Bloquer un Robot'));
	$query = $this->db->prepare('UPDATE robots SET locked=true WHERE name=?') ;
	$query->execute(array($var));
	$message='Le Robot à bien été bloqué';
	$this->view->message('Robot bloqué' , $message, '/admin');
}

public function deblock($var){
	$this->view->assign(array('pageTitle' => 'Débloquer un Robot'));
	$query = $this->db->prepare('UPDATE robots SET locked=false WHERE name=?') ;
	$query->execute(array($var));
	$message='Le Robot à bien été bloqué';
	$this->view->message('Robot débloqué' , $message, '/admin');
}

public function showNotAllowed(){
	$message='Vous n\'êtes pas autorisé à aller sur cette page';
	$this->view->message('Erreur' , $message, '/home');
}

public function takeControl($var){
	$this->view->assign(array('pageTitle' => 'Contôler un Robot'));
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.name=? AND used=false ') ;
	$query->execute(array($var));
	$value2 = $query->fetch(PDO::FETCH_ASSOC);
	$value3=$value2['id'];
	$query = $this->db->prepare('INSERT INTO games (refmember,refrobot,starttime) VALUES(?,?,?)') ;
	$query->execute(array($this->user->id,$value3,time()));
	$query = $this->db->prepare('UPDATE robots SET used=true WHERE robots.id=? ') ;
	$query->execute(array($value3));
	$message='Vous êtes redirigé sur la page de jeu';
	$this->view->message('Redirection' , $message, '/play/play');
}

public function delRobot($var){
	$this->view->assign(array('pageTitle' => 'Supprimer un robot'));
	$query = $this->db->prepare('SELECT robots.id FROM robots WHERE robots.name=? AND used=false ') ;
	$query->execute(array($var));
	$value2 = $query->fetch(PDO::FETCH_ASSOC);
	$value3=$value2['id'];
	$query = $this->db->prepare('DELETE FROM robots WHERE robots.name=?') ;
	$query->execute(array($var));
	$message='Le robot à bien été supprimé';
	$this->view->message('Robot supprimé' , $message, '/admin');
}

public function setNotUsed($var){
	$this->view->assign(array('pageTitle' => 'Enlever l\'utilisation d\'un robot'));
	$query = $this->db->prepare('UPDATE robots SET used=false WHERE name=?') ;
	$query->execute(array($var));
	$query = $this->db->prepare('SELECT refmember FROM games WHERE refrobot=(SELECT robots.id from robots INNER JOIN games ON refrobot=robots.id WHERE robots.name=?)') ;
	$query->execute(array($var));
	$id = $query->fetch(PDO::FETCH_ASSOC);
	$query = $this->db->prepare('DELETE FROM games WHERE refmember=?') ;
	$query->execute(array($id['refmember']));
	$message='Le Robot à bien été désassocié';
	$this->view->message('Robot désassocié' , $message, '/admin');
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
	}
	
	if($var['modifyCtrip']){
		$query = $this->db->prepare('UPDATE robots SET ctrip=? WHERE id=? ') ;
		$query->execute(array($var['modifyCtrip'],$var['modify']));
	}
	
	if($var['modifyCtrport']){
		$query = $this->db->prepare('UPDATE robots SET ctrport=? WHERE id=? ') ;
		$query->execute(array($var['modifyCtrport'],$var['modify']));
	}
	$message='Les modifications sont effectuées';
	$this->view->message('Modifications effectuées' , $message, '/admin');
}

public function addRobot($var){
	print_r($var);
	print($var['addCtrip']);
	print($var['addCtrport']);
	print($var['addName']);
	if($var['addName'] && $var['addCtrip'] && $var['addCtrport'] && $var['addId']){
	$this->view->assign(array('pageTitle' => 'Ajouter un Robot'));
	$query = $this->db->prepare('INSERT INTO robots (id,name,ctrip,ctrport) VALUES (?,?,?,?)') ;
	$query->execute(array($var['addId'],$var['addName'],$var['addCtrip'],$var['addCtrport']));
	$message='Le robot est ajouté';
	$this->view->message('Robot ajouté' , $message, '/admin');
	}
	else{
		$message='Vous n\'avez pas entrée de valeur pour tout les champs';
		$this->view->message('Erreur', $message, '/admin');
	}
}

public function addAdmin($var){
	$value=$var['addAdmin'];
	$query = $this->db->prepare('SELECT members.id FROM members WHERE members.pseudo=?') ;
	$query->execute(array($value));
	$value2 = $query->fetch(PDO::FETCH_ASSOC);
	$value3=$value2['id'];
	$query = $this->db->prepare('INSERT INTO admin refmember) VALUES (?');
	$query->execute(array($value3));
}
}