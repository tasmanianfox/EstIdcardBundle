<?php
namespace TFox\EstIdcardBundle\Security\User;


use TFox\EstIdcardBundle\Entity\ClientData;
class EstIdcardUser implements EstIdcardUserInterface
{
	
	/**
	 * @var string
	 */
	protected $personalCode;
	
	/**
	 * @var string
	 */
	protected $firstName;
	
	/**
	 * @var string
	 */
	protected $lastName;
	
	/**
	 * @var array
	 */
	protected $roles;
	
	/**
	 * @var ClientData 
	 */
	protected $certificateData;
	
	public function __construct(ClientData $clientData)
	{
		$this->firstName = $clientData->getFirstName();
		$this->lastName = $clientData->getLastName();
		$this->personalCode = $clientData->getPersonalCode();
		$this->roles = array('ROLE_USER');
		$this->certificateData = $clientData;
	}
	
	public function eraseCredentials()
	{

	}
	
	public function getUsername()
	{
		return $this->personalCode;
	}
	
	public function getSalt()
	{
		return null;
	}
	
	public function getPassword()
	{
		return null;
	}
	
	public function getRoles() {
		return $this->roles;
	}
	
	public function getPersonalCode() {
		return $this->personalCode;
	}

	public function getFirstName($ucfirst = true) {
		if($ucfirst)
			return ucfirst(strtolower($this->firstName));
		return $this->firstName;
	}

	public function getLastName($ucfirst = true) {
		if($ucfirst)
			return ucfirst(strtolower($this->lastName));
		return $this->lastName;
	}
	
	public function getCertificateData()
	{
		return $this->certificateData;
	}

	
}
