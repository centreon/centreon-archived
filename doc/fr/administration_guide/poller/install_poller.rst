=======================
Installer un collecteur
=======================

A partir de l'ISO Centreon
--------------------------

Le processus d’installation est identique à celui d’un serveur Centreon
Central réalisé à partir du fichier ISO de Centreon.

.. note::
    Référez-vous à la documentation :ref:`installation<installisoel7>`.

A la question **Which server type would you like to install?** il faut choisir
l’option **Poller server**.

.. image:: /images/guide_utilisateur/configuration/10advanced_configuration/07installpoller.png
    :align: center

Rendez-vous au chapitre :ref:`Echange de clés SSH<sskkeypoller>` pour continuer.

A partir des paquets Centreon
-----------------------------

Afin d’installer les logiciels Centreon à partir des dépôts, vous devez au
préalable installer le fichier lié au dépôt. Exécutez la commande suivante
à partir d’un utilisateur possédant les droits suffisants.

Installation du dépôt Centreon: ::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-1.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-1.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-1.el7.centos.noarch.rpm

Le dépôt est maintenant installé.

Exécutez la command : ::

    # yum install centreon-poller-centreon-engine

Rendez-vous au chapitre :ref:`Echange de clés SSH<sskkeypoller>` pour continuer.
