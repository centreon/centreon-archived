==========================
Installer un Remote Server
==========================

------------
Installation
------------

L'installation d'un Remote Server est très similaire à celle d'un Centreon
Central Server.

A partir de l'ISO Centreon
--------------------------

Suivez la documentation :ref:`A partir de Centreon ISO el7<installisoel7>` pour
installer un Centreon Central server.

A partir des paquets Centreon
-----------------------------

Suivez la documentation :ref:`A partir des paquets Centreon<install_from_packages>`
pour installer un Centreon Central server.

------------------------------
Activer l'option Remote Server
------------------------------

Connectez-vous à votre serveur ayant la fonction **Remote Server** et exécutez
la commande suivante ::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Remplacez **@IP_CENTREON_CENTRAL** par l'IP du serveur Centreon vu par le collecteur.

Cette commande va activer le mode **Remote Server** ::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success
      
      Set 'remote' instance type...Done
      
      Centreon Remote enabling finished.

Rendez-vous au chapitre :ref:`Echange de clés SSH<sskkeypoller>` pour continuer.
