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
        <LocationMatch ".*/secured/login_check$">
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
