.. _install_from_packages:

====================
A partir des paquets
====================

Centreon fournit RPM pour ses produits au travers de la solution Centreon 
Entreprise Server (CES) en version Open Sources et disponible gratuitement 
sur notre dépôt.

Ces paquets ont été testés avec succès sur les environnements CentOS et Red Hat en version 5.x et 6.x.

**********
Prérequis
**********

Afin d'installer les logiciels Centreon à partir des dépôts CES, vous
devez au préalable installer le fichier lié au dépôt. 

Exécuter la commande suivante à partir d'un utilisateur possédant les droits suffisants :

  ::

    $ wget http://yum.centreon.com/standard/3.0/stable/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

Le dépôt est maintenant installé.

Installer un serveur central
----------------------------

Ce chapitre décrit l'installation d'un serveur central Centreon.

Installation du serveur avec le moteur Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-base-config-centreon-engine centreon

Installer un collecteur
-----------------------

Ce chapitre décrit l'installation d'un collecteur.

Installation du serveur avec le moteur Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-poller-centreon-engine

Configuration basique d'un collecteur
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

La communication entre le serveur central et un collecteur se fait via SSH.

Vous devez échanger les clés SSH entre les serveurs.

Si vous n'avez pas de clé SSH privés sur le serveur central pour l'utilisateur 'centreon' :

  ::

  $ su - centreon
  $ ssh-keygen -t rsa

Vous devez copier cette clé sur le collecteur :

  ::

  $ ssh-copy-id centreon@your_poller_ip
