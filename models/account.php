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
		if (!empty($erreurs))
			$this->view->littleError($erreurs, 'Erreur de saisie');
		else{
			// Vérifie que le pseudo est libre
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
	}
	
	public function messageAlreadyLogout(){
		$this->view->redirect('?alreadyLogout');
	}
	
	public function logout(){
		session_unset();
		$this->view->redirect('?WellLogout');
	}
}

?>
