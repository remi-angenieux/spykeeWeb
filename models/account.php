<?php

class AccountModel extends BaseModel
{
	protected $_spykee;

	
	public  function index(){
		$this->view->assign(array('pageTitle' => 'Accueil',
				'username' => $this->user->pseudo));
		$this->view->addAdditionalCss('profil.css');
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
					$this->view->redirect('account/?badAccountSame');
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
					$this->view->redirect('account/?badRegister');
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
		try{
			$query = $this->db->prepare('SELECT pseudo FROM members
								     EXCEPT
									 SELECT pseudo FROM members WHERE id=? OR id=? OR id=?') ;
			$query->execute(array($this->user->id,$this->config->global->banId,$this->config->global->guestId));
			$array2 = $query->fetchAll(PDO::FETCH_ASSOC);
			$this->view->assign('array2',$array2);
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else 
			$this->view->redirect('account/?badListUser');
		}
	}
	
	public function visitProfil($var){
		try{
				$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=?') ;
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
				$this->view->redirect('account/?badProfilUser');
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
					$this->view->redirect('account/?badPass');
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
					$this->view->redirect('account/?badAccountCon');
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
				$this->view->redirect('account/?badProfil');
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
				$this->view->redirect('account/?badChangePass');
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
				$this->view->redirect('account/?badDelHistory');
		}
	
	}
	
	public function changeMail($var){
		
		$query = $this->db->prepare('UPDATE members SET email=? WHERE id=?') ;
		try{
			$query->execute(array($var['email'],$this->user->id));
			$this->view->redirect('account/?wellChangeEmail');
			if(!$this->isMail($var)){
			$this->view->redirect('account/?badEmail');
			}
		}
		catch(PDOException $e){
			if($this->model->isAdmin)
				Error::displayError($e);
			else
				$this->view->redirect('account/?badEmail');
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
				$this->view->redirect('account/?badHistory');
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
			$this->view->redirect('account/?badUploadSize');
					break;
				case 2:
			$this->view->redirect('account/?badUploadSize');
					break;
				case 3:
			$this->view->redirect('account/?badUpload');
					break;
			
				case 4:
			$this->view->redirect('account/?badUpload');
					break;
				case 6:
			$this->view->redirect('account/?badUpload');
					break;
	
				case 7:
			$this->view->redirect('account/?badUpload');
					break;
				case 8:
								
			$this->view->redirect('account/?badUploadWeight');
					break;
			}
		}
		else{
			if ($var['icone']['size'] > $this->config->upload->maximgsize){
				$this->view->redirect('account/?badUploadWeight');
			}
			else{
				$image_sizes = getimagesize($var['icone']['tmp_name']);
				if ($image_sizes[0] > $this->config->upload->maximgwidth OR $image_sizes[1] >$this->config->upload->maximgheight){
					$this->view->redirect('account/?badUploadSize');
				}
				else{
					$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
					$extension_upload = strtolower(  substr(  strrchr($var['icone']['name'], '.')  ,1)  );
						
					if (! in_array($extension_upload,$extensions_valides) ){
						$this->view->redirect('account/?badUploadExt');
					}
					else{
						$imgdir = "{$this->user->global->rootUrl}images/profils/{$this->user->id}.{$extension_upload}";
						$resultat = move_uploaded_file($var['icone']['tmp_name'],$imgdir);
						if ($resultat){
							$query = $this->db->prepare('UPDATE members SET image=? WHERE members.id=?') ;
							try{
								$query->execute(array($this->user->id.".".$extension_upload,$this->user->id));
								$this->view->redirect('account?wellChangeImg');
							}
							catch(PDOException $e){
								if($this->model->isAdmin)
									Error::displayError($e);
								else
									$this->view->redirect('account/?badUpload');
							}
						}
						else{
							$this->view->redirect('account/?badUpload');
						}
						}
						}
					}	
				
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
