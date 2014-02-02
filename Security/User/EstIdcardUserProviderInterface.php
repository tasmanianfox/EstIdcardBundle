<?php

namespace TFox\EstIdcardBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use TFox\EstIdcardBundle\Entity\ClientData;

interface EstIdcardUserProviderInterface extends UserProviderInterface
{
	public function setClientData(ClientData $clientData);
}