EstIdcardBundle
===============

Enables authentication via Estonian national ID card in Symfony 2 projects
Detailed description will be available soon

Installation
===============
Isn't available yet. When the Bundle will be completed, instructions for Composer will be added.

Configuration
===============

As soon as Estonian National ID cards use assymertic key ancryption to authenticate on websites, EstIdcardBundle requires OpenSSL extension installed and enabled on server machine. When ID card authentication is required, Apache (not PHP!) requests client's certificate. After certificate was sent, user's certificate information becomes available from $_SERVER array. Please note that autentication via ID cards is only available if website uses HTTPS protocol.


Your HTTP server should check client's certificate on some specific page. An example of Apache virtual host configuration is provided below:
~~~
#HTTPS-based websites listen port 443. It might be necessary to add NameVirtualHost directive before declaration of virtual host.

<VirtualHost *:443>
        #Here are such common directives as ServerName, ServerAlias, paths to log files, etc 

        #Directives for SSL
        SSLEngine On
        SSLProtocol all -SSLv2
        SSLCipherSuite ALL:!ADH:!EXPORT:!SSLv2:RC4+RSA:+HIGH:+MEDIUM:+LOW
        
        #Information about certificates. More info on http://id.ee/index.php?id=35753
        
        SSLCertificateFile /etc/apache2/conf/ssl.key/mykey.crt
        SSLCertificateKeyFile /etc/apache2/conf/ssl.key/mykey.key
        SSLCACertificateFile /etc/apache2/conf/ssl.key/id_key.crt
        SSLCARevocationPath /etc/apache2/conf/ssl.key/revocation/
        
        #Path to the page where ID card will be checked
        <LocationMatch ".*/id/secured/login_check$">
                SSLVerifyClient optional
                SSLVerifyDepth 2
                SSLOptions +StdEnvVars +ExportCertData
        </LocationMatch>

        <Directory "/var/www/symfony/web">
                Order allow,deny
                Allow from all
                AllowOverride FileInfo All
                Require all granted
        </Directory>

        #Export certificate data to PHP scripts
        <Files ~ "\.(php|cgi|pl)$">
                SSLOptions +StdEnvVars +ExportCertData
        </Files>

</VirtualHost>
~~~

The next step is configuration of Symfony application.  Here is an example of security.yml file:

~~~
#app/config/security.yml

security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    providers:
        id_card: #Built-in user provider which reads personal code, first name and last name directly from ID card. In this case all users have role 'ROLE_USER'
            id: "est_id_card.security.user.provider"
        in_memory: #An example of custom user provider. You can paste your own provider here
            memory:
                users:
                    48206094455:  { roles: [ 'ROLE_USER' ] } #Personal code as user name
                    36009275032: { roles: [ 'ROLE_ADMIN' ] }

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false
            
        #An error page. No authentication is required here.
        id_login:
            pattern:  ^/id/secured/login$
            security: false
                
        est.id_card_secured:
#You can choose your own user provider here
#            provider: in_memory
            provider: id_card
            pattern:    ^/id/secured
            est_id_card:
                login: acme_demo_id_default_login #Path to page with error messages
                login_check: acme_demo_id_default_logincheck #Path to page where authentication data is verified

    access_control:
        - { path: ^/id/secured/login_check, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https } #Client's certificate is checked here
        - { path: ^/id/secured/login, roles: IS_AUTHENTICATED_ANONYMOUSLY, requires_channel: https } #User will be redirected to this page if authentication error is occured
        - { path: ^/id/secured/admin, roles: ROLE_ADMIN, requires_channel: https } #some role is required here
        - { path: ^/id/secured, roles: ROLE_USER, requires_channel: https } #some another role is required here
~~~

Usage
=======================================

An example of controller where user's privileges are checked:

~~~
<?php
//src/Acme/DemoBundle/Controller/Id/DefaultController.php

namespace Acme\DemoBundle\Controller\Id;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use TFox\EstIdcardBundle\Security\Firewall\EstIdcardListener;

/**
 * @Sensio\Route
 */
class DefaultController extends Controller
{
	
	/**
	 * Unsecured area
	 * @Sensio\Route
	 * @Sensio\Template
	 */
	public function indexAction()
	{
		return array();
	}
	
	/**
	 * Secured area. Requires ROLE_USER role to pass. Displays information about user (watch secure.html.twig file)
	 * @Sensio\Route("/secured")
	 * @Sensio\Template
	 */
	public function secureAction(Request $request)
	{
		/* @var $securityContext \Symfony\Component\Security\Core\SecurityContextInterface */
		$securityContext = $this->get('security.context');
		if(!$securityContext->isGranted('ROLE_USER'))
			throw new AccessDeniedHttpException();
		/* @var $token \TFox\EstIdcardBundle\Security\Authentication\Token\EstIdcardToken */
		$token = $securityContext->getToken();
		$user = $token->getUser();

		return array('user' => $user);
	}
	
	/**
	 * The purpose of this action is to display errors which might occur as a result of unsuccessful authentication
	 * @Sensio\Route("/secured/login")
	 */
	public function loginAction(Request $request)
	{
	        //Check if any authentication exception accured before calling of loginAction
		$exception = $this->get('session')->get(EstIdcardListener::SESSION_AUTH_EXCEPTION);
		if($exception instanceof \Exception)
			throw $exception;
			
		//If no errors found, redirect to authentication action
		return $this->redirect($this->generateUrl('tfox_test_id_default_logincheck'));
	}
	
	/**
	 * If user if authenticated successfully, redirect him to main page
	 * NB: The Bundle performs all checks before call of this action.
	 * The code provided below only can be reached if client entered a direct URI for this page
	 * @Sensio\Route("/secured/login_check")
	 */
	public function loginCheckAction(Request $request)
	{
		return $this->redirect($this->generateUrl('tfox_test_id_default_secure'));
	}
	
	/**
	 * Another one secured action. If configuration above wasn't changed, it should throw AccessDeniedHttpException, because there are no users with role ROLE_ADMIN
	 * @Sensio\Route("/secured/admin")
	 * @Sensio\Template
	 */
	public function adminAction()
	{
		/* @var $securityContext \Symfony\Component\Security\Core\SecurityContext */
		$securityContext = $this->get('security.context');
		if(!$securityContext->isGranted('ROLE_ADMIN'))
			throw new AccessDeniedHttpException();
		return array();
	}
}
~~~

There are example twig files for controller above:

~~~

{# src/Acme/DemoBundle/Resources/views/Id/Default/index.html.twig #}

Unsecured area

~~~

~~~

{# src/Acme/DemoBundle/Resources/views/Id/Default/admin.html.twig #}

Admin area

~~~

~~~

{# src/Acme/DemoBundle/Resources/views/Id/Default/secure.html.twig #}

{# If built-in user provider is in use, such methods as getFirstName, getLastName and getPersonalCode are available for User object #}
{% if user.firstName is defined and user.lastName is defined %}
	Welcome to secured area, {{ user.firstName }} {{ user.lastName }}! Your personal code is {{ user.personalCode }}
{% else %}
{# For custom providers all necessary properties should be defined manually #}
	Welcome to secured area, {{ user.username }}!
{% endif %}

~~~

