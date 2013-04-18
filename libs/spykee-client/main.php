<?php

class SpykeeClient
{
	/*
	 * Actions
	*/
	const TURNLEFT = 1;
	const TURNRIGHT = 2;
	const FORWARD = 3;
	const BACK = 4;
	const STOP = 5;
	const STOPSERVER = 13;
	const MOVE = 'MV';

	/*
	 * Etats de l'action
	*/
	const STATEOK = 1;
	const STATEERROR = 0;



	/*
	 * Définition des attributs
	*/
	private $_serverPort;
	private $_robotName;
	private $_serverIp;
    private $_sock;
    
    function __construct($robotName, $serverIp, $serverPort)
    {
    	$this->_serverPort = $serverPort;
    	$this->_robotName = $robotName;
    	$this->_serverIp = $serverIp;
    	$this->connectToTheServer();
    }


	private function connectToTheServer()
	{
		//Creation of the socket
		if(!($this->_sock = socket_create(AF_INET, SOCK_STREAM, 0))) //Can't connect
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			die("Couldn't create socket: [$errorcode] $errormsg \n");
		}

		echo "Socket created \n";                            //Connected

		if(!socket_connect($this->_sock, $this->_serverIp, $this->_serverPort))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			die("Could not connect: [$errorcode] $errormsg \n");
		}

		echo "Connection established \n";
	}


	public function sendAction($message)
	{
		
		if(!socket_send($this->_sock ,$message , strlen($message) , 0))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			die("Could not send data: [$errorcode] $errormsg \n");
		}

		echo "Message send successfully \n";
		
		if(socket_recv($this->_sock, $response, 1, MSG_WAITALL) === FALSE ){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			
			die("Could not send data: [$errorcode] $errormsg \n");
		}
		echo 'Reponse : '.$response;

	}


	private function closeSocket()
	{
		socket_close($this->_sock);
		/*if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
		{

			die("Socket has been succefully closed \n");
		}*/

	}



	public function forward()  //Tout droit
	{
		$this->sendAction(self::FORWARD);
	}
	public function backward()  //en Arriere
	{
		$this->sendAction(self::BACKWARD);
	}


	public function turnLeft() //Tourne a gauche
	{
		$this->sendAction(self::TURNLEFT);
	}
	public function turnRight() //Tourne a droite
	{
		$this->sendAction(self::TURNRIGHT);
	}

	function __destruct(){
		$this->closeSocket();
	}


}


?>