<?php
class SpykeeResponse{
	
	const NO_DESCRIPTION = 0;
	
	const NUMBER_RECONNEXION_EXCEEDED = 1;
	const ERROR_SEND_PAQUET = 2;
	const PAQUET_SENT = 3;
	const ERROR_RECEIVE_PAQUET = 4;
	const CONNECTION_REINIT = 5;
	const UNABLE_READ_DATA = 6;
	
	const RECEIVE_PAQUET_TYPE_AUDIO = 7;
	const RECEIVE_PAQUET_TYPE_VIDEO = 8;
	const RECEIVE_PAQUET_TYPE_POWER = 9;
	const RECEIVE_PAQUET_TYPE_AUTH_REPLY = 10;
	const RECEIVE_PAQUET_TYPE_STOP = 11;
	const RECEIVE_PAQUET_TYPE_WIRELESS_NETWORKS = 12;
	const RECEIVE_PAQUET_TYPE_CONFIG = 13;
	const RECEIVE_PAQUET_TYPE_LOG = 14;
	const RECEIVE_PAQUET_UNKNOW = 15;
	
	const LEVEL_BATTERY_RETRIVED = 16;
	const TOO_MANY_CONNECTION = 17;
	
	protected $_state=NULL;
	protected $_data=NULL;
	protected $_idDescription=NULL;
	protected $_description=NULL;
	
	public function __construct($state, $idDescription=self::NO_DESCRIPTION, $data=''){
		$this->_state = $state;
		$this->_idDescription = $idDescription;
		$this->_data = $data;
	}
	
	public function getState(){
		return $this->_state;
	}
	
	public function getIdDescription(){
		return $this->_idDescription;
	}
	
	public function getDescription(){
		// Si le texte de la description à déjà été défini
		if (!empty($this->_description))
			return $this->_description;
		// Si aucune description n'a été definie
		if (empty($this->_idDescription)){
			$this->_description = 'Aucune description a été défini';
			return $this->_description;
		}
		// Sinon on le cherche via l'id de la description
		switch($this->_idDescription){
			case self::NUMBER_RECONNEXION_EXCEEDED:
				$this->_description = 'Nombre de tentative de reconnexion dépassé';
				break;
			case self::ERROR_SEND_PAQUET:
				$this->_description = 'Impossible d\'envoyer le paquet';
				break;
			case self::PAQUET_SENT:
				$this->_description = 'Le paquet à bien été envoyé';
				break;
			case self::ERROR_RECEIVE_PAQUET:
				$this->_description = 'Problème de réception du paquet';
				break;
			case self::CONNECTION_REINIT:
				$this->_description = 'Connexion réinitialisée';
				break;
			case self::UNABLE_READ_DATA:
				$this->_description = 'Impossible de lire les données véhiculé dans le paquet';
				break;
				
			case self::RECEIVE_PAQUET_TYPE_AUDIO:
				$this->_description = 'Paquet reçu de type audio';
				break;
			case self::RECEIVE_PAQUET_TYPE_VIDEO:
				$this->_description = 'Paquet reçu de type vidéo';
				break;
			case self::RECEIVE_PAQUET_TYPE_POWER:
				$this->_description = 'Paquet reçu de type niveau de batterie';
				break;
			case self::RECEIVE_PAQUET_TYPE_AUTH_REPLY:
				$this->_description = 'Paquet reçu de type réponse d\'authentification';
				break;
			case self::RECEIVE_PAQUET_TYPE_STOP:
				$this->_description = 'Paquet reçu de type stop';
				break;
			case self::RECEIVE_PAQUET_TYPE_WIRELESS_NETWORKS:
				$this->_description = 'Paquet reçu de type connexion sans fil';
				break;
			case self::RECEIVE_PAQUET_TYPE_CONFIG:
				$this->_description = 'Paquet reçu de type configuration';
			break;
			case self::RECEIVE_PAQUET_TYPE_LOG:
				$this->_description = 'Paquet reçu de type log';
			break;
			case self::RECEIVE_PAQUET_UNKNOW:
				$this->_description = 'Paquet reçu de type inconnu';
				break;
				
			case self::LEVEL_BATTERY_RETRIVED:
				$this->_description = 'Niveau de batterie récupéré';
				break;
			case self::TOO_MANY_CONNECTION:
				$this->_description = 'Le nombre maximum de connexion simultannées à été atteint';
				break;
				
			case self::NO_DESCRIPTION:
			default:
				$this->_description = 'Aucune description a été défini';
				break;
				
			return $this->_description;
		}
		
		return $this->_description;
	}
	
	public function getData(){
		return $this->_data;
	}
	
	public function jsonFormat(){
		$return = '{';
		$return .= '"state": '.$this->getState().', ';
		$return .= '"data": "'.$this->getData().'", ';
		$return .= '"description": "'.$this->getDescription().'", ';
		$return .= '"idDescription": '.$this->getIdDescription();
		$return .= '}';
		return $return;
	}
}


?>