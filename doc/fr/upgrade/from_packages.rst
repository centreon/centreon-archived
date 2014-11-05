.. _upgrade_from_packages:

====================
A partir des paquets
====================

.. warning::
    Avant de mettre à jour Centreon, veuillez sauvegarder vos bases de données.

************************************************************
Mise à jour depuis une version antérieure à la version 2.4.0
************************************************************

La structure des RPM a changé entre la version Centreon 2.3.x et la version 2.4.x.

Avant de mettre à jour Centreon vous devez choisir entre deux modèles :
``Centreon Engine and Centreon Broker`` ou ``Nagios and Ndo2db``.

Ce choix est dépendant de votre moteur de supervision.

Modèle Centreon Engine
**********************

Lancer la commande :
  ::

    # yum update centreon centreon-base-config-centreon-engine

Modèle Nagios
*************

Lancer la commande :
  ::

     # yum update centreon centreon-base-config-nagios

