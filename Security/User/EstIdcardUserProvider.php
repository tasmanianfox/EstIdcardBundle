<?php

namespace TFox\EstIdcardBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use TFox\EstIdcardBundle\Service\CertificateReaderService;
use TFox\EstIdcardBundle\Entity\ClientData;
use Symfony\Component\HttpFoundation\Session\Session;
use TFox\EstIdcardBundle\Security\Firewall\EstIdcardListener;

class EstIdcardUserProvider implements EstIdcardUserProviderInterface
{
	/**
	 * @var \TFox\EstIdcardBundle\Entity\ClientData
	 */
	protected $clientData;	
	
	/**
	 * @var \Symfony\Component\HttpFoundation\Session\Session
	 */
	protected $session;
	
	public function __construct(Session $session)
	{
		$this->session = $session;
	}
	
	public function loadUserByUsername($username)
	{
		$clientData = $this->session->get(EstIdcardListener::SESSION_CLIENT_DATA);
		return new EstIdcardUser($clientData);
	}
	
	public function setClientData(ClientData $clientData)
	{
		$this->clientData = $clientData;	
	}
	
	public function supportsClass($class)
	{
		return 'TFox\EstIdcardBundle\Security\User\EstIdcardUser' == $class;
	}
	
	/**
	 * @param TFox\EstIdcardBundle\Security\User\EstIdcardUserInterface $user
	 */
	public function refreshUser(UserInterface $user)
	{
		if($this->supportsClass(get_class($user)))
			return $this->loadUserByUsername($user->getPersonalCode());
	}
}