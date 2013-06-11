<?php

class AccountModel extends BaseModel
{
	protected $_spykee;

	
	public  function index(){
		$this->view->assign(array('pageTitle' => 'Accueil',
				'username' => $this->user->pseudo));
		$this->view->addAdditionalCss('profil.css');
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
	
	public function showProfile(){
		$this->view->assign('pageTitle', 'Votre profil');
	}
	
	
	public function showRegister(){
		$this->view->assign('pageTitle', 'Inscription');
	}
	
	public function processRegister($values){
		$values['pseudo'] = trim($values['pseudo']);
		$values['e-mail'] = trim($values['e-mail']);
		/*
		 * Recherche les éventuels erreur de saisies
		 */
		$erreurs='';
		if (empty($values['pseudo']))
			$erreurs .= 'Vous devez renseigner un pseudo.<br />';
		if (empty($values['password']) OR empty($values['password2']))
			$erreurs .= 'Vous devez renseigner votre mot de passe.<br />';
		else if ($values['password'] != $values['password2'])
			$erreurs .= 'Les deux mot de passe ne sont pas identiques.<br />';
		if (empty($values['e-mail']))
			$erreurs .= 'Vous devez renseigner votre addrese mail.<br />';
		else if (!$this->isEmail($values['e-mail']))
			$erreurs .= 'L\'adresse mail entrée n\'est pas une addresse mail valide.<br />';
		
		// Si il y a des erreurs
		if (!empty($erreurs)){
			$this->view->littleError($erreurs, 'Erreur de saisie');
			$this->showRegister();
		}
		else{
			// Vérifie que le pseudo est libre
			try{
				$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=?');
				$query->execute(array($values['pseudo']));
				$result = $query->fetchAll(PDO::FETCH_ASSOC);
				if (count($result) >= 1){
					$message = 'Nous somme désolé mais ce pseudo est déjà utilisé.';
					$this->view->littleError($message);
				}
				else{
					$query = $this->db->prepare('INSERT INTO members (pseudo, password, email) VALUES (?, ?, ?)');
					$query->execute(array($values['pseudo'], sha1($values['password']), $values['e-mail']));
					
					$this->view->redirect('?wellRegistred');
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
				Error::displayError($e);
				else
					$this->view->littleError('Erreur dans votre inscription.');
				}
				
			}
		}
	
	
	public function isEmail($string){
		if (preg_match('#[a-zA-Z0-9\.\-]{3,}@[a-zA-Z0-9\.\-]{3,}\.[a-z]{2,4}#', $string) == 1)
			return true;
		else
			return false;
	}
	
	public function messageAlreadyConnected(){
		$this->view->redirect('?alreadyLogin');
	}
	

	public function displayUser(){
		$query = $this->db->prepare('SELECT pseudo FROM members 
								     EXCEPT
									 SELECT pseudo FROM members WHERE id=? OR id=? OR id=?') ;
		
		try{
			$query->execute(array($this->user->id,$this->config->global->banId,$this->config->global->guestId));
			$array2 = $query->fetchAll(PDO::FETCH_ASSOC);
			$this->view->assign('array2',$array2);
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else 
			$this->view->littleError('Erreur dans le chargement de la liste des utlisateurs.');
		}
	}
	
	public function visitProfil($var){
		$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=?') ;
		try{
				$query->execute(array($var['pseudo']));
				$array1 = $query->fetchAll(PDO::FETCH_ASSOC);
				foreach($array1 as $key=>$value){
				foreach($value as $key2=>$value2){
				$resultat=$value2;
			}
			return $resultat;
		}
		
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le chargement du profil de l\'utilisateur.');
		}
	}
	
	public function showLogin(){
		$this->view->assign('pageTitle', 'Connexion');
	}
	
	public function processLogin($values){
		/*
		 * Recherche les éventuels erreur de saisies
		 */
		$erreurs='';
		if (empty($values['pseudo']))
			$erreurs .= 'Vous devez renseigner votre pseudo.<br />';
		if (empty($values['password']))
			$erreurs .= 'Vous devez renseigner votre mot de passe.<br />';
		
		// Si il y a des erreurs
		if (!empty($erreurs))
			$this->view->littleError($erreurs, 'Erreur de saisie');
		else{
			try{
				$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=? AND password=?');
				$query->execute(array($values['pseudo'], sha1($values['password'])));
				$response = $query->fetchAll(PDO::FETCH_ASSOC);
				
				// Si la connexion à échoué
				if (count($response) != 1){
					$message = 'Vous avez du vous tromper dans le pseudo ou le mot de passe.';
					$this->view->littleError($message);
				}
				else{
					$_SESSION['id'] = $response[0]['id'];
					$this->view->redirect('?WellLogin');
				}
			}
			catch (PDOException $e){
				if($this->model->isAdmin)
					Error::displayError($e);
				else
					$this->view->littleError('Erreur dans la connexion a votre compte.');
			}
		}
	}
	
	public function messageAlreadyLogout(){
		$this->view->redirect('?alreadyLogout');
	}
	
	public function logout(){
		session_unset();
		$this->view->redirect('?WellLogout');
	}
	


	
	public function displayProfil(){
		$this->view->assign(array('pageTitle' => 'Profil'));
		$query = $this->db->prepare('SELECT pseudo,email FROM members WHERE id=?') ;
		try{
				$query->execute(array($this->user->id));
				$array = $query->fetchAll(PDO::FETCH_ASSOC);
				foreach($array as $key=>$value){
				foreach($value as $key2=>$value2){
				$array1[]=$value2;
			}
		}
			$this->view->assign('array',$array);
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le chargement du profil.');
		}

	
	}
	
	public function changePass($var){
		$query = $this->db->prepare('UPDATE members SET password=? WHERE id=?') ;
		try{
		$query->execute(array(sha1($var['pass']),$this->user->id));
		$this->view->redirect('account/?wellChangePass');
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le changement du mot de passe.');
		}

	}
	
	public function delHistory(){
		$query = $this->db->prepare('DELETE FROM gameshistory WHERE refmember=?') ;
		try{
			$query->execute(array($this->user->id));
			$this->view->redirect('account/?wellDelHistory');
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans la suppression de l\'historique.');
		}
	
	}
	
	public function changeMail($var){
		
		$query = $this->db->prepare('UPDATE members SET email=? WHERE id=?') ;
		try{
			$query->execute(array(sha1($var['email']),$this->user->id));
			$this->view->redirect('account/?wellChangeEmail');
			if(!$this->isMail($var)){
			$this->view->littleError('Erreur dans le changement de votre e-mail.');
			}
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le changement de votre e-mail.');
		}
	
	}
	
	public function displayHistory(){
		$this->view->assign(array('pageTitle' => 'Historique'));
		$query = $this->db->prepare('SELECT robots.name,duration,date FROM gameshistory INNER JOIN robots ON robots.id=refrobot WHERE gameshistory.refmember=?') ;
		try{
			$query->execute(array($this->user->id));
			$array1 = $query->fetchAll(PDO::FETCH_ASSOC);
			foreach($array1 as $key=>$value){
				foreach($value as $key2=>$value2){
					$resultat[]=$value2;
				}
			}
		$this->view->assign('array1',$array1);
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le chargement de l\historique.');
		}

	
	}
	
	public function uploadImg($var,$var1){
		$var['icone']['name'];     //Le nom original du fichier
		$var['icone']['type'];     //Le type du fichier
		$var['icone']['size'];     //La taille du fichier en octets.
		$var['icone']['tmp_name']; //L'adresse vers le fichier
		$var['icone']['error'];    //Le code d'erreur
		if ($var['icone']['error'] > 0) {
		switch($var['icone']['error']){
				case 1:
			$this->view->littleError('L\'image est trop volumineuse :(');
					break;
				case 2:
			$this->view->littleError('L\'image est trop volumineuse :(');
					break;
				case 3:
			$this->view->littleError('Le fichier n\'été que partiellement téléchargé :(');
					break;
			
				case 4:
			$this->view->littleError('Un dossier est manquant :(');
					break;
				case 6:
			$this->view->littleError('Echec de l\'écriture des fichiers :(');
					break;
	
				case 7:
			$this->view->littleError('Une extension PHP a arrêté l\'envoi de fichier , contacter un administrateur :(');
					break;
				case 8:
								
			$this->view->littleError('L\'image est trop volumineuse :(');
					break;
			}
		}
		if ($var['icone']['size'] > $this->config->upload->maximgsize){
			$this->view->littleError('L\'image est trop volumineuse :(');
		}
	
		$image_sizes = getimagesize($var['icone']['tmp_name']);
			if ($image_sizes[0] > $this->config->upload->maximgwidth OR $image_sizes[1] >$this->config->upload->maximgheight){
			$this->view->littleError('L\'image est trop volumineuse :(');
		}
	
		$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
			$extension_upload = strtolower(  substr(  strrchr($var['icone']['name'], '.')  ,1)  );
	
		if (! in_array($extension_upload,$extensions_valides) ){
			$this->view->littleError('L\'extension de l\'image est invalide :(');
		}
	
		$imgdir = "{$this->user->global->rootUrl}images/profils/{$this->user->id}{$extension_upload}";
		$resultat = move_uploaded_file($var['icone']['tmp_name'],$imgdir);
		if ($resultat){
			$query = $this->db->prepare('UPDATE members SET image=? WHERE members.id=?') ;
			try{
				$query->execute(array($this->user->id.".".$extension_upload,$this->user->id));
				$this->view->redirect('?wellChangeImg');
			}
			catch(PDOException $e){
				if($this->model->isAdmin)
					Error::displayError($e);
				else
					$this->view->littleError('Erreur dans l\'upload de votre image :(');
			}
		}
		else{
			$message = 'L\'upload de l\'image a échoué ';
			$this->view->littleError('L\'upload de l\'image échoué.');
			}
		}
	
	
	public function displayImg(){
		$imgDir="{$this->config->global->rootUrl}images/profils/";
		$query = $this->db->prepare('SELECT image FROM members WHERE id=?');
		try{
			$query->execute(array($this->user->id));
			$array = $query->fetch(PDO::FETCH_ASSOC);
			$resultat=$array['image'];
			if(!$resultat){
				$src=null;
			}
			else{
				$src=$imgDir.$resultat;
			}
				$this->view->assign('src',$src);
			}
			catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->littleError('Erreur dans le chargement de votre image :(');
		}

	}
	
	
	
}

?>
