<?php

namespace TFox\EstIdcardBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ClientCertificateReadingException extends AuthenticationException
{
	public function __construct()
	{
		$this->message = 'Cannot read client certificate';
	}
	
}