.. _install_from_vm:

================
A partir des VMs
================

Deux Machines virtuelles pré-configurées sont disponibles sur le site de téléchargement de Centreon.

Ces machines virtuelles sont disponibles au format OVF et sont validées pour des architectures VMWare. La procédure décrite ici a été validée sur la version 5.1 du client VSphere.

Import
------

Il est dans un premier temps nécessaire d'importer le ficher OVF. Pour cela, sur le client VSphere allez dans le menu **File > Deploy OVF Template** et sélectionnez le ficher précedemment téléchargé.
Il est alors nécessaire de suivre les différents menus. Les différents choix sont trés liés à l'infrastructure VMWare en place, il est difficile d'être rééllement spécifique dans cette documentation.
Il est important de noter que les bonnes pratiques sont de choisir le mode **Thin Provision** qui vous permettra d'économiser de l'espace disque sur vos datastores.

Connexion
---------

La machine est configurée avec des comptes par défaut.

Vous pourrez donc vous connecter à l'interface web avec le compte : **admin/centreon**.
Vous pourez aussi vous connecter en SSH avec le compte **root/centreon**.
Le mot de passe de de l'utilisateur **root** de la base de données n'est pas initialisé.

.. note::
    Pour des raisons de sécurité, il est recommandé de changer tous ces mots
    de passe aprés l'installation

A la première connexion, un message indique les opérations à réaliser. Suivez
celles-ci, **spécialement les opérations 4 et 5**.

.. note::
    Pour supprimer ce message, supprimez le fichier **/etc/profile.d/centreon.sh**.

Collecteur
----------

L'installation d'un collecteur de supervision (poller) est trés similaire à celle du central de supervision. Il est juste nécessaire d'ajouter à cette procédure l'échange des clefs SSH et la configuration sur l'interface Web.

Echange des clefs SSH
=====================

La communication entre le serveur central et un collecteur se fait via SSH.

Vous devez échanger les clés SSH entre les serveurs.

Si vous n’avez pas de clé SSH privées sur le serveur central pour
l’utilisateur ‘centreon’ ::

    # su - centreon
    $ ssh-keygen -t rsa

Vous devez copier cette clé sur le nouveau serveur : ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

le mot de passe de l'utilisateur centreon sur le collecteur est configuré par défaut à **centreon**. Il est fortement conseillé de la changer en utilisant la commande **passwd**.

Configuration de l'interface Web
================================

#. Dans le menu **Configuration > Poller > Pollers**, activez le **Poller Template** et remplacez IP_POLLER par l'adresse IP de votre poller.
#. Dans le menu **Configuration > Poller > Engine configuration**, activez **Poller-template**
#. Dans le menu **Configuration > Poller > Broker configuration**, activez **Poller-template-module** et dans l'onglet **Output** remplacez **IP_CENTRAL** par l'adresse IP de votre serveur central.

Vous pouvez maintenant ajouter des éléments à superviser sur votre collecteur, il est fonctionnel !

.. warning::

    La premiére fois que vous exportez la configuration, il sera nécessaire de choisir l'option **restart**.
