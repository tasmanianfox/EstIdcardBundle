<?php

namespace TFox\EstIdcardBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use TFox\EstIdcardBundle\Entity\ClientData;

class EstIdcardToken extends AbstractToken 
{
	/**
	 * @var \TFox\EstIdcardBundle\Entity\ClientData
	 */
	private $clientData = null;
	
	public function __construct($roles = array())
	{
		parent::__construct($roles);
		$this->setAuthenticated(count($roles) > 0);
	}
	
	public function setClientData(ClientData $clientData)
	{		
		$this->clientData = $clientData;
	}
	
	public function getCredentials() 
	{
		return '';
	}
	
	public function getPersonalCode()
	{
		if(!$this->clientData instanceof ClientData)
			return null;
		return $this->clientData->getPersonalCode();
	}
}