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

CES 3.1 (CentOS 6.6)
--------------------

Exécuter la commande suivante à partir d'un utilisateur possédant les droits suffisants :

  ::

    $ wget http://yum.centreon.com/standard/3.0/stable/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

Le dépôt est maintenant installé.

CES 2.2 (CentOS 5.x)
--------------------

Exécuter la commande suivante à partir d'un utilisateur possédant les droits suffisants :

  ::

    $ wget http://yum.centreon.com/standard/2.2/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

Le dépôt est maintenant installé.


************************
Installation de Centreon
************************

Depuis CES 2.2, deux choix d'installation sont disponibles

+--------------------------------------+-----------------------+-----------------+
| Nom du paquet de configuration       | Moteur de supervision | Module broker   |
+======================================+=======================+=================+
| centreon-base-config-centreon-engine | Centreon Engine       | Centreon Broker |
+--------------------------------------+-----------------------+-----------------+
| centreon-base-config-nagios          | Nagios                | NDOutils        |
+--------------------------------------+-----------------------+-----------------+

Vous devez choisir entre l'un des deux processus de configuration de votre 
plate-forme de supervision. Centreon recommande le premier choix basé sur le 
moteur "Centreon Engine" et le multiplexeur de flux "Centreon Broker".

Installer un serveur central
----------------------------

Ce chapitre décrit l'installation d'un serveur central Centreon.

Installation du serveur avec le moteur Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-base-config-centreon-engine centreon

Installation du serveur avec le moteur Nagios
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-base-config-nagios centreon

Après cette étape, vous devez vous connecter à Centreon pour finaliser le processus
d'installation. Cette étape est décrite :ref:`ici <installation_web>`.

Installer un collecteur
-----------------------

Ce chapitre décrit l'installation d'un collecteur.

Installation du serveur avec le moteur Centreon Engine
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-poller-centreon-engine

Installation du serveur avec le moteur Nagios
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

Exécutez la commande :

  ::

  $ yum install centreon-poller-nagios

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
