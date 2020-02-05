*************************************************
Configurer un nouveau Remote Server dans Centreon
*************************************************

Depuis Centreon 18.10, un assistant de configuration est disponible pour ajouter
un Remote Server à la plate-forme Centreon.

.. note::
    Il est possible d'ajouter un nouveau collecteur :ref:`manuellement<add_manual_poller>`,
    cependant Centreon recommande d'utiliser la procédure suivante.

Rendez-vous au menu **Configuration > Pollers** et cliquez sur **Add server with
wizard** pour accéder à l’assistant de configuration.

Sélectionnez **Add a Centreon Remote Server** et cliquez sur **Next** :

.. image:: /images/poller/wizard_add_remote_1.png
    :align: center

Si vous avez activé votre serveur en suivant la documentation, sélectionnez
l'option **Select a Remote Server**. Dans la liste déroulante sélectionnez
votre serveur, puis saisissez les informations demandées :

.. image:: /images/poller/wizard_add_remote_2a.png
    :align: center

Sinon, sélectionnez l'option **Create new Remote Server**
et saisissez les informations demandées.

.. image:: /images/poller/wizard_add_remote_2b.png
    :align: center

Les champs **Database user** et **Database password** sont les accès aux bases
de données Centreon définis durant l'installation de votre Remote Server.

Le champ **Server IP address** est de la forme : [(http|https)://]@IP[:(port)].
Si votre Remote Server est accessible en HTTPS, il est nécessaire de préciser
la méthode d'accès et le port si celui-ci n'est pas par défaut.

L'option **Do not check SSL certificate validation** permet de contacter le
Remote Server si celui-ci possède un certificat SSL auto-signé.

L'option **Do not use configured proxy tp connect to this server** permet
de de contacter le Remote Server en n'utilisant pas la configuration
du proxy configurée sur le serveur Centreon Central.

Cliquez sur **Next** :

Sélectionnez le(s) collecteur(s) à lier à ce Remote Server. Puis cliquez sur
**Apply** :

.. image:: /images/poller/wizard_add_remote_3.png
    :align: center

L'assistant va configurer votre nouveau serveur :

.. image:: /images/poller/wizard_add_remote_4.png
    :align: center

Une fois la configuration exportée, redémarrez le processus Centreon Broker
sur le Remote Server via la commande suivante : ::

    # systemctl restart cbd

Le Remote Server est maintenant configuré :

.. image:: /images/poller/wizard_add_remote_5.png
    :align: center

Rendez-vous au chapitre :ref:`Démarrage rapide<quickstart>` pour mettre en place votre première supervision.
