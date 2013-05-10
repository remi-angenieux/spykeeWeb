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
			$this->view->message('Erreur de saisie', $erreurs, '/account/register');
		else{
			// Vérifie que le pseudo est libre
			$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=?');
			$query->execute(array($values['pseudo']));
			$result = $query->fetchAll(PDO::FETCH_ASSOC);
			if (count($result) >= 1){
				$message = 'Nous somme désolé mais ce pseudo est déjà utilisé.';
				$this->view->message('Pseudo déjà utilisé', $message, '/account/register');
			}
			else{
				$query = $this->db->prepare('INSERT INTO members (pseudo, password, email) VALUES (?, ?, ?)');
				$query->execute(array($values['pseudo'], sha1($values['password']), $values['e-mail']));
				
				$message = 'Votre inscription s\'est bien déroulée, vous pouvez dorénavant vous connecter.';
				$this->view->message('Inscription réussi', $message, '/account/login');
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
		$message = 'Vous êtes dejà connecté. Cette action vous est inutile.<br>
				Si vous voulez faire quand même cette action il faut que vous vous déconnectiez.';
		$url='/'; // Accueil
		$this->view->message('Vous êtes déjà connecté', $message, $url);
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
			$this->view->message('Erreur de saisie', $erreurs, '/account/login');
		else{
			$query = $this->db->prepare('SELECT id FROM members WHERE pseudo=? AND password=?');
			$query->execute(array($values['pseudo'], sha1($values['password'])));
			$response = $query->fetchAll(PDO::FETCH_ASSOC);
			
			// Si la connexion à échoué
			if (count($response) != 1){
				$message = 'Vous avez du vous tromper dans le pseudo ou le mot de passe.';
				$this->view->message('Connexion échoué', $message, '/account/login');
			}
			else{
				$_SESSION['id'] = $response[0]['id'];
				$message = 'Vous avez bien été connecté ;)';
				$this->view->message('Connexion réussie', $message, '/');
			}
		}
	}
	
	public function messageAlreadyLogout(){
		$message = 'Vous êtes déjà déconnecté';
		$this->view->message('Vous êtes déjà déconnecté', $message, '/');
	}
	
	public function logout(){
		session_unset();
		$message = 'Vous avez bien été déconnecté.';
		$this->view->message('Deconnexion réussie', $message, '/');
	}
}

?>
