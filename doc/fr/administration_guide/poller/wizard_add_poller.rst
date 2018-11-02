**********************************************
Configurer un nouveau collecteur dans Centreon
**********************************************

Depuis Centreon 18.10, un assistant de configuration est disponible pour ajouter
un collecteur à la plate-forme Centreon.

Choisissez le chapitre suivant le type de serveur.

.. note::
    Il est possible d'ajouter un nouveau collecteur :ref:`manuellement<add_manual_poller>`,
    cependant Centreon recommande d'utiliser la procédure suivante.

Rendez-vous dans le menu **Configuration > Pollers** et cliquez sur **Add server
with wizard** pour accéder à l'assistant de configuration.

Sélectionnez **Add a Centreon Poller** et cliquez sur **Next** :

.. image:: /images/poller/wizard_add_poller_1.png
    :align: center

Saisissez le nom, l'adresse IP du collecteur et celle du serveur Centreon central,
cliquez sur **Next** :

.. image:: /images/poller/wizard_add_poller_2.png
    :align: center

.. note::
    L'adresse IP du collecteur est l'adresse IP ou le FQNS pour accéder au
    collecteur depuis le serveur Centreon Central.
    
    L'adresse IP du collecteur est l'adresse IP ou le FQNS pour accéder au
    collecteur serveur Centreon Central vers le collecteur.

Si vous souhaitez lier ce collecteur au serveur Centreon Central, cliquez
sur **Apply** :

.. image:: /images/poller/wizard_add_poller_3.png
    :align: center

Sinon, si vous souhaitez lier ce collecteur à un Remote Server, sélectionnez
le Remote Server dans la liste et cliquez sur **Apply** :

.. note::
    Si vous souhaitez changer le sens de connexion des données entre le serveur
    Centreon central et le collecteur, cochez la case **Advanced: reverse Centreon
    Broker communication flow**.

Patientez quelques secondes, l'assistant va configurer votre nouveau serveur.

Le collecteur est maintenant configuré :

.. image:: /images/poller/wizard_add_poller_5.png
    :align: center

Rendez-vous au chapitre :ref:`Configuration simplifiée avec Centreon IMP<impconfiguration>`
pour mettre en place votre première supervision.
