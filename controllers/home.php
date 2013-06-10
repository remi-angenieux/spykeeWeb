<?php
class HomeController extends BaseController
{
	//add to the parent constructor
	public function __construct($action, $urlValues) {
		parent::__construct($action, $urlValues);

		//create the model object
		require(PATH.'models/home.php');
		$this->model = new HomeModel($this->view);
	}

	//default method
	protected function index(){
		$this->model->index();
		// Gestion de l'affichage des messages
		if(isset($this->urlValues['wellRegistred']))
			$this->view->littleMessage('Votre inscription s\'est bien déroulée, vous pouvez dorénavant vous connecter.');
		else if(isset($this->urlValues['alreadyLogin']))
			$this->view->littleMessage('Vous êtes déjà connecté');
		else if(isset($this->urlValues['WellLogin']))
			$this->view->littleMessage('Vous avez bien été connecté ;)');
		else if(isset($this->urlValues['alreadyLogout']))
			$this->view->littleMessage('Vous êtes déjà déconnecté');
		else if(isset($this->urlValues['WellLogout']))
			$this->view->littleMessage('Vous avez bien été déconnecté.');
	}

	protected function admin(){
		
		$this->model->admin();
	}

}

?>
