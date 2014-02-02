<?php

namespace TFox\EstIdcardBundle\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

interface EstIdcardUserInterface extends UserInterface
{
	public function getPersonalCode();

	public function getFirstName();

	public function getLastName();
}