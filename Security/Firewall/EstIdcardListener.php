<?php

namespace TFox\EstIdcardBundle\Security\Firewall;

use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use TFox\EstIdcardBundle\Security\Authentication\Token\EstIdcardToken;
use Symfony\Component\HttpFoundation\Response;
use TFox\EstIdcardBundle\Exception\ClientCertificateReadingException;
use TFox\EstIdcardBundle\Service\CertificateReaderService;

class EstIdcardListener implements ListenerInterface 
{
	/**
	 * @var \Symfony\Component\Security\Core\SecurityContextInterface
	 */
    protected $securityContext;
    
    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;
    
    /**
     * @var \TFox\EstIdcardBundle\Service\CertificateReaderService
     */
    protected $certificateReader;
    
    

    public function __construct(SecurityContextInterface $securityContext, 
    		AuthenticationManagerInterface $authenticationManager,
			CertificateReaderService $certificateReader)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        $this->certificateReader = $certificateReader;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $token = new EstIdcardToken();

        try {
        	$serverAttributes = $request->server->all();

        	/**
			 * @var $clientData \TFox\EstIdcardBundle\Entity\ClientData
        	 */
        	$clientData = $this->certificateReader->readCertificate($request);
        	$token->setClientData($clientData);
        	
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);

        }
        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}