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
security:
    encoders:
        Symfony\Component\Security\Core\User\User: plaintext

    role_hierarchy:
        ROLE_ADMIN:       ROLE_USER
        ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

    providers:
        id_card: #Built-in user provider which reads personal code, first name and last name directly from ID card
            id: "est_id_card.security.user.provider"
        in_memory: #An example of custom user provider. You can paste your own provider here
            memory:
                users:
                    48206094455:  { roles: [ 'ROLE_USER' ] }
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
