<?php

class SpykeeClient
{
	private function createSocket()
	{
		//Creation of the socket
		if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0))) //Can't connect
		{
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			die("Couldn't create socket: [$errorcode] $errormsg \n");
		}
		
		echo "Socket created \n";                            //Connected
	}
	
		//Connection to the server
		private function connectServer()
	{
			if(!socket_connect($sock , '74.125.235.20' , 80))
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
	
		

	private function forward()  //Tout droit
	{
		$message=0;
	}
	private function backWard()  //en Arriere
	{
		$message=1;
	}


	private function turnLeft() //Tourne a gauche
	{
		$message=2;
	}
	private function turnRight() //Tourne a droite
	{
		$message=3;
	}

	private function ledOn()    //Allume la led
	{
		$message=4;
	}
	

	
}


?>