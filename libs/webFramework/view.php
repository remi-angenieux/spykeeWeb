<?php

require_once(PATH.'libs/smarty/Smarty.class.php');


class View extends Smarty{

	protected $_viewFile;
	protected $_additionalCss=array();
	protected $_additionalJs=array();
	protected $_controllerName;
	protected $_action;
	protected $_headerFile;
	protected $_footerFile;
	protected $_config;
	protected static $_instance;

	public function __construct($controllerClass, $action)
	{
		parent::__construct();

		$this->_controllerName = strtolower(str_replace('Controller', '', $controllerClass));
		$this->_action = $action;
		$this->_config=Config::getInstance();

		$this->setTemplateDir($this->_config->template->templateDir);
		$this->setCompileDir($this->_config->template->compileDir);
		$this->setConfigDir($this->_config->template->configsDir);
		$this->setCacheDir($this->_config->template->cacheDir);

		$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->force_compile = $this->_config->template->forceCompile;
		
		// Header et footer par défaut
		$this->_headerFile=$this->_config->template->defaultHeader;
		$this->_footerFile=$this->_config->template->defaultFooter;
		$this->assign('rootUrl', $this->_config->global->rootUrl);
		self::$_instance = $this;

	}
	
	public function addAdditionalCss($file){
		array_push($this->_additionalCss, $this->_config->global->rootUrl.'css/'.$file);
	}
	
	public function addAdditionalJs($file){
		array_push($this->_additionalJs, $this->_config->global->rootUrl.'js/'.$file);
	}
	
	public function setEnvironement($env){
		switch($env){
			case 'empty':
				$this->_headerFile='';
				$this->_footerFile='';
				break;
			case 'home':
				$this->_headerFile='extras/html_home_header';
				$this->_footerFile='extras/html_home_footer';
				break;
		}
	}
	
	public function setTemplate($file='', $controller=''){
		$file = (!empty($file)) ? $file : $this->_action;
		$controller = (!empty($controller)) ? $controller : $this->_controllerName;
		
		$this->_viewFile = $controller.'/'.$file;
	}
	
	public function message($title, $message, $url=''){
		$this->_viewFile = 'extras/message';
		$this->assign('pageTitle', 'Message');
		$this->assign(array('title' => $title,
				'message' => $message,
				'url' => $url
		));
	}
	
	public function littleMessage($message, $title='Info :'){
		$this->assign('littleMessage', $message);
		$this->assign('littleMessageTitle', $title);
	}
	
	public function littleError($message, $title='Erreur :'){
		$this->assign('littleError', $message);
		$this->assign('littleErrorTitle', $title);
	}
	
	// Standalone fonction
	static function displayError($message, $title='Erreur :'){
		self::$_instance->assign('littleError', $message);
		self::$_instance->assign('littleErrorTitle', $title);
	}
	
	public function redirect($page){
		header('Location: '.$this->_config->global->rootUrl.$page);
	}
	
	public function __destruct(){
		
		$this->assign('additionalCss', $this->_additionalCss);
		$this->assign('additionalJs', $this->_additionalJs);
		
		if (empty($this->_viewFile))
			$this->setTemplate();
		$this->assign(array('tpl_header' => $this->_headerFile,
				'tpl_body' => $this->_viewFile,
				'tpl_footer' => $this->_footerFile
		));
		// Affiche la page web si il y a pas eu une erreur fatale
		if (!Error::getFatalError())
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