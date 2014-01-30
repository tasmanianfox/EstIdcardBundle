<?php

namespace TFox\EstIdcardBundle\Security\Authentication\Provider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use TFox\EstIdcardBundle\Security\Authentication\Token\EstIdcardToken;
use TFox\EstIdcardBundle\Exception\ClientCertificateReadingException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class EstIdcardAuthenticationProvider implements AuthenticationProviderInterface 
{
    private $userProvider;
    private $cacheDir;

    public function __construct(UserProviderInterface $userProvider, $cacheDir)
    {
        $this->userProvider = $userProvider;
        $this->cacheDir     = $cacheDir;
    }

    /* @var $token \TFox\EstIdcardBundle\Security\Authentication\Token\EstIdcardToken */
    public function authenticate(TokenInterface $token)
    {
    	$user = null;
		try {
			$user = $this->userProvider->loadUserByUsername($token->getPersonalCode());
		} catch(UsernameNotFoundException $e) {}
		
        if (!is_null($user)) {
            $authenticatedToken = new EstIdcardToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new ClientCertificateReadingException();
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof EstIdcardToken;
    }
}