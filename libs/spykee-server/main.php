<?php

class SpykeeServer{
	
	/*
	 * Actions
	 */
	const TURNLEFT = 1;
	const TURNRIGHT = 2;
	
	private $_stopServer = false;
	private $_ipAddress;
	
	function __construct($ipAddress){
		$this->_ipAddress = $ipAddress;
		
		$this->connectionToTheRobot();
		
		$this->readActions();
	}
	
	private function connectionToTheRobot(){
		
		socket('x95'.$this->_ipAddress);
	}
	
	private function readActions(){
		while(!$this->_stopServer){
			
		}
	}
	
}

?>