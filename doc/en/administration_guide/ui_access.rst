========================
Accessing to Centreon UI
========================

**********
Custom URI
**********

It is possible to update the URI of Centreon. For example, **/centreon** can be replaced by **/monitoring**.

To update the Centreon URI, you need to follow those steps:

1. Go to **Administration > Parameters > Centreon UI** and change the **Centreon Web Directory** value.

.. image:: /_static/images/adminstration/custom_uri.png
    :align: center

2. On the centreon central server:

* Replace **/centreon** occurences by **/your_custom_uri** in **centreon/www/.htaccess**.
* Navigate to your Centreon URL.

************
HTTPS access
************

To access to the UI using HTTPS, follow those steps:

1. Install SSL module for Apache: ::

    # yum install httpd24-mod_ssl openssl

2. Install your certificats or generate self-signed certificates :

* /etc/pki/tls/certs/ca.crt
* /etc/pki/tls/private/ca.key

3. Backup previous Apache configuration for Centreon: ::

    # cp /opt/rh/httpd24/root/etc/httpd/conf.d/10-centreon.conf{,.origin}

4. Then edit the file as following: ::

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
