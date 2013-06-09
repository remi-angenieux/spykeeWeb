<?php
class Error {
	protected static $_fatalError=false;
	
	static function displayError($message, $title='Erreur :'){
		View::displayError($message, $title);
		if(self::$_fatalError){
			$page = '<!DOCTYPE html>
					  <html lang="fr">
					    <head>
					      <meta charset="utf-8" />
					      <title>ERREUR !</title>
					    </head>
						<body>
							<h1>Erreur fatal !</h1>
							<p>Un erreur très importante est survenue a la base du site ce qui le rend infonctionnel.<br />
							Veuillez nous en excuser.<br />
							<br />Plus d\'informations sont disponibles dans les logs du serveur</p>
						</body>
					  </html>';
			echo $page;
			trigger_error($message, E_USER_ERROR); // Pour que le message soit visible dans les logs et stopper le script
		}
	}
	static function setFatalError($bool){
		if ($bool == true)
			self::$_fatalError = true;
	}
	static function getFatalError(){
		return self::$_fatalError;
	}
}