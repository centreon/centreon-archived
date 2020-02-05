******************************
Activer l'option Remote Server
******************************

Connectez-vous à votre serveur ayant la fonction **Remote Server** et exécutez
la commande suivante ::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v '@IP_CENTREON_CENTRAL;<not check SSL CA on Central>;<HTTP method>;<TCP port>;<not check SSL CA on Remote>;<no proxy to call Central>'

.. note::
    Remplacez **@IP_CENTREON_CENTRAL** par l'IP du serveur Centreon vu par le collecteur.
    Vous pouvez définir plusieurs adresses IP en utilisant la virgule comme séparateur.

.. note::
    * Pour utiliser HTTPS, remplacez **@IP_CENTREON_CENTRAL** par
      **https://@IP_CENTREON_CENTRAL**.
    * Pour utiliser un autre port TCP, remplacez **@IP_CENTREON_CENTRAL** par
      **@IP_CENTREON_CENTRAL:<port>**.

Pour ne pas contrôler le certificat SSL sur le serveur Centreon Central,
mettre à **1** l'option **<not check SSL CA on Central>**, sinon **0**.

L'option **<HTTP method>** permet de définir la méthode de connexion pour
contacter le Remote Server : HTTP ou HTTPS.

L'option **<TCP port>** permet de définir sur quel port TCP communiquer avec le
Remote Server.

Pour ne pas contrôler le certificat SSL sur le Remote server, mettre à **1**
l'option **<not check SSL CA on Central>**, sinon **0**.

Pour ne pas utiliser le proxy pour contacter le serveur Centreon Central,
mettre à **1** l'option **<no proxy to call Central>**, sinon **0**.

Cette commande va activer le mode **Remote Server** ::

    Starting Centreon Remote enable process:
    Limiting Menu Access...               Success
    Limiting Actions...                   Done
    Authorizing Master...                 Done
    Set 'remote' instance type...         Done
    Notifying Master...
    Trying host '10.1.2.3'... Success
    Centreon Remote enabling finished.

Ajout des droits pour que l'utilisateur de base de données centreon puisse utiliser la commande **LOAD DATA INFILE**::

    # mysql -u root -p
    MariaDB [(none)]> GRANT FILE on *.* to 'centreon'@'localhost';
