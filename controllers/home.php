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
		//$this->view->output($this->model->index());
		$this->model->index();
		//$this->view->display('mainTemplate.tpl');
	}

	protected function admin(){
		
		$this->model->admin();
	}

}

?>