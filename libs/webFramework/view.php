<?php

require_once('smarty/Smarty.class.php');


class View extends Smarty{

	protected $_viewFile;
	protected $_additionalCss=array();
	protected $_additionalJs=array();

	public function __construct($controllerClass, $action)
	{
		parent::__construct();

		$controllerName = str_replace('Controller', '', $controllerClass);
		$this->_viewFile = strtolower($controllerName) . '/' . $action;

		$this->setTemplateDir(PATH.'views/');
		$this->setCompileDir(PATH.'views_c/');
		$this->setConfigDir(PATH.'configs/');
		$this->setCacheDir(PATH.'cache/');

		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		// Pour la phase de développement
		$this->force_compile = TRUE;

		$this->assign(array('tpl_header' => 'extras/header',
				'tpl_body' => $this->_viewFile,
				'tpl_footer' => 'extras/footer',
		));
	}
	
	public function addAdditionalCss($file){
		array_push($this->_additionalCss, $file);
	}
	
	public function addAdditionalJs($file){
		array_push($this->_additionalJs, $file);
	}
	

	public function __destruct(){
		
		$this->assign('additionalCss', $this->_additionalCss);
		$this->assign('additionalJs', $this->_additionalJs);
		// Affiche la page web
		$this->display('mainTemplate.tpl');

		parent::__destruct();
	}
}

/*class View {

protected $viewFile;

//establish view location on object creation
public function __construct($controllerClass, $action) {
$controllerName = str_replace("Controller", "", $controllerClass);
$this->viewFile = "views/" . $controllerName . "/" . $action . ".php";
}

//output the view
public function output($viewModel, $template = "maintemplate") {

$templateFile = "views/".$template.".php";

if (file_exists($this->viewFile)) {
if ($template) {
//include the full template
if (file_exists($templateFile)) {
require($templateFile);
} else {
require("views/error/badtemplate.php");
}
} else {
//we're not using a template view so just output the method's view directly
require($this->viewFile);
}
} else {
require("views/error/badview.php");
}

}
}*/

?>