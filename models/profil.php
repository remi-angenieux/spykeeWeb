<?php
class ProfilModel extends BaseModel
{

	public function index(){


	}

	public function displayProfil(){
	$this->view->assign(array('pageTitle' => 'Panneau d\'Administrateur'));
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
		$this->view->message('Succés' , $message, '/profil');
	}
	
	
	public function showNotConnected(){
		$this->view->assign(array('pageTitle' => 'Erreur'));
		$message = 'Vous devez être connecté pour pouvoir regarder votre profil';
		$this->view->message('Vous n\' êtes pas connecté' , $message, '/account/login');
	
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
		print_r($var);
		$var['icone']['name'];     //Le nom original du fichier
		$var['icone']['type'];     //Le type du fichier
		$var['icone']['size'];     //La taille du fichier en octets.
		$var['icone']['tmp_name']; //L'adresse vers le fichier 
		$var['icone']['error'];    //Le code d'erreur
		if ($var['icone']['error'] > 0) {
			switch($var['icone']['error']){
				case 1:
					$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/profil');
					break;
				case 2:
					$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/profil');
					break;
				case 3:
					$message = 'Le fichier n\'a été que partiellement téléchargé';
			$this->view->message('Erreur' , $message, '/profil');
					break;
					
				case 4:
					$message = 'Un dossier temporaire est manquant';
			$this->view->message('Erreur' , $message, '/profil');
					break;
				case 6:
					$message = 'Échec de l\'écriture du fichier sur le disque';
			$this->view->message('Erreur' , $message, '/profil');
					break;
						
				case 7:
					$message = 'Une extension PHP a arrêté l\'envoi de fichier , contacter un administrateur';
			$this->view->message('Erreur' , $message, '/profil');
					break;
				case 8:
					$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/profil');
					break;								
			}
		}
		if ($var['icone']['size'] > $this->config->maximgsize){	
			$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/profil');
		}
		
		$image_sizes = getimagesize($var['icone']['tmp_name']);
		if ($image_sizes[0] > $this->config->maximgwidth OR $image_sizes[1] >$this->config->maximgheight){
			$message = 'L\'image est trop volumineuse';
			$this->view->message('Erreur' , $message, '/profil');
		}
	
		$extensions_valides = array( 'jpg' , 'jpeg' , 'gif' , 'png' );
		$extension_upload = strtolower(  substr(  strrchr($var['icone']['name'], '.')  ,1)  );
		
		if (! in_array($extension_upload,$extensions_valides) ){
			$message = 'L\extension de l\'image est incorrecte';
			$this->view->message('Erreur' , $message, '/profil');
		}
		
		$imgdir = "D:/Dev-web/spykee/spykeeweb/www/images/{$this->user->id}.{$extension_upload}";
		$resultat = move_uploaded_file($var['icone']['tmp_name'],$imgdir);
		if ($resultat){
			$query = $this->db->prepare('UPDATE members SET image=? WHERE members.id=?') ;
			$query->execute(array($this->user->id.".".$extension_upload,$this->user->id));
			$message = 'L\'upload de l\'image est réussi :)';
			$this->view->message('Succés' , $message, '/profil');
		}
		else{
			$message = 'L\'upload de l\'image a échoué ';
			$this->view->message('Erreur' , $message, '/profil');
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