<?php

class SpykeeClient
{
	function __construct($robotName, $robotIp, $serverPort='')
	{
		self::$_noRobot++;
		$this->_serverPort = ($serverPort=='') ? self::FIRSTPORT + self::$_noRobot : $serverPort;
		$this->_robotName = $robotName;
		$this->_robotIp = $robotIp;
		date_default_timezone_set(self::TIMEZONE);
		$this->connectToTheServer();
	}


	/*
	 * Actions
	*/
	const TURNLEFT = 1;
	const TURNRIGHT = 2;
	const FORWARD = 3;
	const BACKWARD = 4;
	const LEDON=5;
	const STOPCLIENT = 13;

	/*
	 * Etats de l'action
	*/
	const STATEOK = 0;
	const STATEERROR = 1;

	/*
	 * Configuration
	*/
	const TIMEZONE = 'Europe/Paris';
	const MAXCONNECTION = 10;
	const SERVEURIP = '127.0.0.1';

	/*
	 * D�finition des attributs
	*/
	private $_serverPort;
	private $_stopclient = false;
	private $_robotName;
	private $_robotIp;
	private $_logFile;



	private function connectToTheServer()
	{
		//Creation of the socket
		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) //Can't connect
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			die("Couldn't create socket: [$errorcode] $errormsg \n");
		}

		echo "Socket created \n";                            //Connected

		if(!socket_connect($sock , 'SERVERIP' ,$serverport))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
				
			die("Could not connect: [$errorcode] $errormsg \n");
		}

		echo "Connection established \n";
	}


	private function sendAction()
	{
		if( ! socket_send ( $sock , $message , strlen($message) , 0))
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);

			die("Could not send data: [$errorcode] $errormsg \n");
		}

		echo "Message send successfully \n";

	}


	private function closeSocket()
	{
		socket_close($sock);
		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
		{

			die("Socket has been succefully closed \n");
		}

	}



	public function forward()  //Tout droit
	{
		sendAction(FORWARD);
	}
	public function backward()  //en Arriere
	{
		sendAction(BACKWARD);
	}


	public function turnLeft() //Tourne a gauche
	{
		sendAction(TURNLEFT);
	}
	public function turnRight() //Tourne a droite
	{
		sendAction(TURNRIGHT);
	}

	private function ledOn()    //Allume la led
	{
		sendAction(LEDON);
	}



}


?>