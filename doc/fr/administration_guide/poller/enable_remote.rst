******************************
Activer l'option Remote Server
******************************

Connectez-vous à votre serveur ayant la fonction **Remote Server** et exécutez
la commande suivante ::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL;<not check SSL CA on Central>;<HTTP method>;<TCP port>;<not check SSL CA on Remote>;

.. note::
    Remplacez **@IP_CENTREON_CENTRAL** par l'IP du serveur Centreon vu par le collecteur.
    Vous pouvez définir plusieurs adresse IP en utilisant la virgule comme séparateur.

.. note::
    * Pour utiliser HTTPS, remplacez **@IP_CENTREON_CENTRAL** par
      **https://@IP_CENTREON_CENTRAL**.
    * Pour utilsier un autre port TCP, remplacez **@IP_CENTREON_CENTRAL** par
      **@IP_CENTREON_CENTRAL:<port>**.

Pour ne pas contrôler le sertificat SSL sur le serveur Centreon Central,
mettre à **1** l'option **<not check SSL CA on Central>**, sinon **0**.

L'option **<HTTP method>** permet de définir la méthode de connexion pour
contacter le Remote Server : HTTP ou HTTPS.

L'option **<TCP port>** permet de définir sur quel port TCP communiquer avec le
Remote Server.

Pour ne pas contrôler le sertificat SSL sur le Remote server, mettre à **1**
l'option **<not check SSL CA on Central>**, sinon **0**.

Cette commande va activer le mode **Remote Server** ::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success
      
      Set 'remote' instance type...Done
      
      Centreon Remote enabling finished.

