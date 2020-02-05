=============================
A partir des paquets Centreon
=============================

************
Installation
************

SELinux doit être désactivé. Pour cela vous devez modifier le fichier */etc/selinux/config*
et remplacer "enforcing" par "disabled" comme dans l’exemple suivant ::

    SELINUX=disabled

.. note::
    Après avoir sauvegardé le fichier, veuillez redémarrer votre système
    d’exploitation pour prendre en compte les changements.

Une vérification rapide permet de confirmer le statut de SELinux ::

    $ getenforce
    Disabled

Paramétrer le pare-feu système ou désactiver ce dernier. Pour désactiver
ce dernier exécuter les commandes suivantes ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

Afin d’installer les logiciels Centreon à partir des dépôts, vous devez au
préalable installer le fichier lié au dépôt. Exécutez la commande suivante
à partir d’un utilisateur possédant les droits suffisants.

Installation du dépôt Centreon: ::

    # yum install -y http://yum.centreon.com/standard/19.04/el7/stable/noarch/RPMS/centreon-release-19.04-1.el7.centos.noarch.rpm

Le dépôt est maintenant installé.

Exécutez la commande : ::

    # yum install centreon-poller-centreon-engine

.. include:: ssh_key.rst

.. include:: wizard_add_poller.rst
