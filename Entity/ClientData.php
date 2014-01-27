<?php

namespace TFox\EstIdcardBundle\Entity;

class ClientData 
{

	/**
	 * @var string
	 */
	private $firstName;
	
	/**
	 * @var string
	 */
	private $lastName;
	
	/**
	 * @var string
	 */
	private $personalCode;
	
	/**
	 * @var array
	 */
	private $rawData;
	
	/**
	 * @return string
	 */
	public function getFirstName() {
		return $this->firstName;
	}
	
	/**
	 * @param string $firstName
	 * @return \TFox\EstIdcardBundle\Entity\ClientData
	 */
	public function setFirstName($firstName) {
		$this->firstName = $firstName;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getLastName() {
		return $this->lastName;
	}
	
	/**
	 * @param string $lastName
	 * @return \TFox\EstIdcardBundle\Entity\ClientData
	 */
	public function setLastName($lastName) {
		$this->lastName = $lastName;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getPersonalCode() {
		return $this->personalCode;
	}
	
	/**
	 * @param string $personalCode
	 * @return \TFox\EstIdcardBundle\Entity\ClientData
	 */
	public function setPersonalCode($personalCode) {
		$this->personalCode = $personalCode;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getRawData() {
		return $this->rawData;
	}
	
	/**
	 * @param array $rawData
	 * @return \TFox\EstIdcardBundle\Entity\ClientData
	 */
	public function setRawData($rawData) {
		$this->rawData = $rawData;
		return $this;
	}
}