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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;

class EstIdcardListener implements ListenerInterface 
{
	/**
	 * @var \Symfony\Component\Security\Core\SecurityContextInterface
	 */
    protected $securityContext;
    
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;
    
    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     */
    protected $session;
    
    /**
     * @var \Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface
     */
    protected $authenticationManager;
    
    /**
     * @var \TFox\EstIdcardBundle\Service\CertificateReaderService
     */
    protected $certificateReader;
    
    /**
     * @var $string
     */
    protected $loginPath;
    
    /**
     * @var $string
     */
    protected $loginCheckPath;
    
    /**
     * A session key for variable which saves user's original URI before redirection
     * @var string
     */
    const SESSION_REDIRECTED_FROM_URI = 'est_idcard.redirected_from.route';
    
    /**
     * A session key for variable which saves last thrown exception
     * @var string
     */
    const SESSION_AUTH_EXCEPTION = 'est_idcard.auth.exception';
    
    /**
     * A session key for variable which saves user's data
     * @var string
     */
    const SESSION_USER_DATA = 'est_idcard.user_data';

    public function __construct(SecurityContextInterface $securityContext, 
    		Router $router,
    		Session $session,
    		AuthenticationManagerInterface $authenticationManager,
			CertificateReaderService $certificateReader,
			$loginPath,
    		$loginCheckPath
		)
    {
        $this->securityContext = $securityContext;
        $this->router = $router;
		$this->session = $session;
        $this->authenticationManager = $authenticationManager;
        $this->certificateReader = $certificateReader;
        $this->loginPath = $loginPath;
        $this->loginCheckPath = $loginCheckPath;
    }

    public function handle(GetResponseEvent $event)
    {
    	$token = $this->securityContext->getToken();
    	if($token instanceof EstIdcardToken)
    		return;

    	$request = $event->getRequest();    	   		   		
   		$route = $request->attributes->get('_route');   		
   		if($route == $this->loginCheckPath) {
   			//Clear all exceptions
   			$this->session->remove(self::SESSION_AUTH_EXCEPTION);
   			
   			//Current location is authentication page: check client certificate
   			try {
   				/* @var $clientData \TFox\EstIdcardBundle\Entity\ClientData */
   				$clientData = $this->certificateReader->readCertificate($request);
   				$token = new EstIdcardToken();
   				$token->setClientData($clientData);
   				$this->session->set(self::SESSION_USER_DATA, $token->getClientData());

   				//Authenticate
   				$authToken = $this->authenticationManager->authenticate($token);
   				$this->securityContext->setToken($authToken);
   				
   				//Redirect to 
   				$originalUri = $this->session->get(self::SESSION_REDIRECTED_FROM_URI);
   				$this->session->remove(self::SESSION_REDIRECTED_FROM_URI);
   				if(is_string($originalUri))
   					$event->setResponse(new RedirectResponse($originalUri));
   				return;
   			} catch(\Exception $e) {
   				$this->session->set(self::SESSION_AUTH_EXCEPTION, $e);
   				$response = new RedirectResponse($this->router->generate($this->loginPath));
   				$event->setResponse($response);
   				return;
   			}
   		} else {
   			//Current location is some firewall-protected page: redirect to auth check page
   			$this->session->set(self::SESSION_REDIRECTED_FROM_URI, $request->getUri());
   			$event->setResponse(new RedirectResponse($this->router->generate($this->loginCheckPath)));
   			return;
   		}

        $response = new Response();
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        $event->setResponse($response);
    }
}