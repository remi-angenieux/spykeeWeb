<?php
class SpykeeResponse{
	protected $_state=NULL;
	protected $_data=NULL;
	protected $_description=NULL;
	
	public function __construct($state, $description='', $data=''){
		$this->_state = $state;
		$this->_description = $description;
		$this->_data = $data;
	}
	
	public function getState(){
		return $this->_state;
	}
	
	public function getDescription(){
		return $this->_description;
	}
	
	public function getData(){
		return $this->_data;
	}
}


?>