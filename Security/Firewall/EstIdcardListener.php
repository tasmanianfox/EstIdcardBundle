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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $serverAttributes = $request->server->all();
        $token = new EstIdcardToken();
        $response = null;
//         try {
        	/**
        	 * @var $clientData \TFox\EstIdcardBundle\Entity\ClientData
        	 */
        	$clientData = $this->certificateReader->readCertificate($request);
        	$token->setClientData($clientData);

        	try {
        		$authToken = $this->authenticationManager->authenticate($token);
        		$this->securityContext->setToken($authToken);

        		return;
        	} catch(AuthenticationException $e) {
        		throw new AccessDeniedHttpException();
        	}


//         } catch(\Exception $e) {
//         	$response = new Response();
//         	$response->setStatusCode(Response::HTTP_FORBIDDEN);
//         	$event->setResponse($response);
//         	return;
        	
//         	throw new AuthenticationException();
//         }
        /*
        } catch(ClientCertificateReadingException $e) {
        	//$this->securityContext->setToken($token);
			//return;
        } catch (AuthenticationException $failed) {}
*/

        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}