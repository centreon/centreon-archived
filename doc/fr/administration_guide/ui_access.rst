===================================
Accès à l'interface web de Centreon
===================================

*****************
URI personnalisée
*****************

Il est possible de modifier l'URI de Centreon. Par exemple, **/centreon** peut être remplacé par **/monitoring**.

Pour mettre à jour l'URI Centreon, vous devez suivre les étapes suivantes:

1. Rendez-vous dans le menu **Administration > Parameters > Centreon UI** et modifier le champ **Centreon Web Directory**

.. image:: /_static/images/adminstration/custom_uri.png
    :align: center

2. Sur le serveur Centreon :

* Remplacez les occurences **/centreon** par **/votre_uri_personnalise** dans **centreon/www/.htaccess**
* Naviguez vers l'URL Centreon

*******************
Accès en mode HTTPS
*******************

Pour accéder à l'interface web Centreon en mode HTTPS, réaliser les actions
suivantes :

1. Installez le module SSL pour Apache : ::

    # yum install httpd24-mod_ssl openssl

2. Installez vos certificats, ou générez des certificats auto-signés :

* /etc/pki/tls/certs/ca.crt
* /etc/pki/tls/private/ca.key

3. Sauvegarder votre configuration Apache pour Centreon : ::

    # cp /opt/rh/httpd24/root/etc/httpd/conf.d/10-centreon.conf{,.origin}

4. Editez la configuration comme suivant : ::

    Alias /centreon /usr/share/centreon/www/

    <LocationMatch ^/centreon/(.*\.php(/.*)?)$>
      ProxyPassMatch fcgi://127.0.0.1:9042/usr/share/centreon/www/$1
    </LocationMatch>
    ProxyTimeout 300

    <LocationMatch ^/centreon/api/(latest/|beta/|v[0-9]+/|v[0-9]+\.[0-9]+/)(.*)$>
      ProxyPassMatch fcgi://127.0.0.1:9042/usr/share/centreon/api/index.php/$1
    </LocationMatch>

    <VirtualHost *:80>
      RewriteEngine On
      RewriteCond %{HTTPS} off
      RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}
    </VirtualHost>

    <VirtualHost *:443>
      SSLEngine on
      SSLCertificateFile /etc/pki/tls/certs/ca.crt
      SSLCertificateKeyFile /etc/pki/tls/private/ca.key

      <Directory "/usr/share/centreon/www">
        DirectoryIndex index.php
        Options Indexes
        AllowOverride all
        Order allow,deny
        Allow from all
        Require all granted
        <IfModule mod_php5.c>
          php_admin_value engine Off
        </IfModule>

        AddType text/plain hbs
      </Directory>

      <Directory "/usr/share/centreon/api">
        Options Indexes
        AllowOverride all
        Order allow,deny
        Allow from all
        Require all granted
        <IfModule mod_php5.c>
          php_admin_value engine Off
        </IfModule>

        AddType text/plain hbs
      </Directory>
    </VirtualHost>

    RedirectMatch ^/$ /centreon
