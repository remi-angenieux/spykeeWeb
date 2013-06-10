<?php

class AccountModel extends BaseModel
{
	protected $_spykee;
	
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
				Error::displayError($e);
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
									 SELECT pseudo FROM members INNER JOIN admin ON refmember = id
									 EXCEPT
									 SELECT pseudo FROM members WHERE id=?') ;
		$query->execute(array($this->user->id));
		$array2 = $query->fetchAll(PDO::FETCH_ASSOC);
		$this->view->assign('array2',$array2);	
	}
	
	public function visitProfil($var){
		$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=?') ;
		$query->execute(array($var['pseudo']));
		$array1 = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($array1 as $key=>$value){
			foreach($value as $key2=>$value2){
				$resultat[]=$value2;
			}
		}
		return $resultat;
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
				Error::displayError($e);
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
		$query->execute(array($this->user->id));
		$array = $query->fetchAll(PDO::FETCH_ASSOC);
	
		foreach($array as $key=>$value){
			foreach($value as $key2=>$value2){
				$array1[]=$value2;
			}
		}
		$this->view->assign('array',$array);
	
	}
	
	public function changePass($var){
		$query = $this->db->prepare('UPDATE members SET password=? WHERE id=?') ;
		$query->execute(array(sha1($var['pass']),$this->user->id));
		$message='Mot de passe changé avec succés';
		$this->view->message('Succés' , $message, '/account');
	}
	
	public function delHistory(){
		$query = $this->db->prepare('DELETE FROM gameshistory WHERE refmember=?') ;
		$query->execute(array($this->user->id));
		$message='Historique des parties effacés';
		$this->view->message('Succés' , $message, '/account');
	}
	
	public function changeMail($var){
		if($this->isMail($var)){
		$query = $this->db->prepare('UPDATE members SET email=? WHERE id=?') ;
		$query->execute(array(sha1($var['email']),$this->user->id));
		$message='E-mail changé avec succés';
		$this->view->message('Succés' , $message, '/account');
		}
		else{
			$message='Le champ e-mail n\'est pas valide';
			$this->view->message('Erreur' , $message, '/account');
		}
	}
	
	public function history(){
		$this->view->assign(array('pageTitle' => 'Historique'));
		$query = $this->db->prepare('SELECT robots.name,duration,date FROM gameshistory INNER JOIN robots ON robots.id=refrobot WHERE gameshistory.refmember=?') ;
		$query->execute(array($this->user->id));
		$array1 = $query->fetchAll(PDO::FETCH_ASSOC);
		foreach($array1 as $key=>$value){
			foreach($value as $key2=>$value2){
				$resultat[]=$value2;
			}
		}
		$this->view->assign('array1',$array1);
	
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
			$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/account');
					break;
			case 2:
		$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/account');
				break;
						case 3:
						$message = 'Le fichier n\'a été que partiellement téléchargé';
			$this->view->message('Erreur' , $message, '/account');
				break;
			
				case 4:
				$message = 'Un dossier temporaire est manquant';
			$this->view->message('Erreur' , $message, '/account');
				break;
				case 6:
				$message = 'Échec de l\'écriture du fichier sur le disque';
			$this->view->message('Erreur' , $message, '/account');
						break;
	
				case 7:
				$message = 'Une extension PHP a arrêté l\'envoi de fichier , contacter un administrateur';
			$this->view->message('Erreur' , $message, '/account');
						break;
						case 8:
								$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/account');
						break;
		}
		}
		if ($var['icone']['size'] > $this->config->maximgsize){
		$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/account');
		}
	
		$image_sizes = getimagesize($var['icone']['tmp_name']);
			if ($image_sizes[0] > $this->config->maximgwidth OR $image_sizes[1] >$this->config->maximgheight){
			$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/account');
		}
	
		$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
			$extension_upload = strtolower(  substr(  strrchr($var['icone']['name'], '.')  ,1)  );
	
		if (! in_array($extension_upload,$extensions_valides) ){
		$message = 'L\extension de l\'image est incorrecte';
			$this->view->message('Erreur' , $message, '/account');
		}
	
		$imgdir = "D:/Dev-web/spykee/spykeeweb/www/images/{$this->user->id}.{$extension_upload}";
		$resultat = move_uploaded_file($var['icone']['tmp_name'],$imgdir);
		if ($resultat){
			$query = $this->db->prepare('UPDATE members SET image=? WHERE members.id=?') ;
			$query->execute(array($this->user->id.".".$extension_upload,$this->user->id));
				$message = 'L\'upload de l\'image est réussi :)';
				$this->view->message('Succés' , $message, '/account');
		}
		else{
		$message = 'L\'upload de l\'image a échoué ';
		$this->view->message('Erreur' , $message, '/account');
		}
		}
	
	
	public function displayImg(){
		$imgDir="/images/";
	
		$query = $this->db->prepare('SELECT image FROM members WHERE id=?') ;
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
	
	
	
}

?>
