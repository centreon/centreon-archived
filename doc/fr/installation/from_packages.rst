.. _install_from_packages:

===================
A partir des dépôts
===================

Centreon fournit RPM pour ses produits au travers de la solution Centreon
Entreprise Server (CES) en version Open Sources et disponible gratuitement
sur notre dépôt.

Ces paquets ont été testés avec succès sur les environnements CentOS et
Red Hat en version 6.x et 7.x.

*********
Prérequis
*********

Dépôt Centreon
--------------

Afin d'installer les logiciels Centreon à partir des dépôts CES, vous devez au préalable installer le fichier lié au dépôt. Exécuter la commande suivante à partir d'un utilisateur possédant les droits suffisants.

Pour CentOS 6.

::

   $ wget http://yum.centreon.com/standard/3.4/el6/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


Pour CentOS 7.

::

   $ wget http://yum.centreon.com/standard/3.4/el7/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


Le dépôt est maintenant installé.

Pour tous les OS
----------------

SELinux doit être désactivé. Pour cela vous devez modifier le fichier "/etc/sysconfig/selinux" et remplacer "enforcing" par "disabled" comme dans l'exemple suivant :

 ::

 SELINUX=disabled

La timezone par défaut de PHP doit être configurée. Pour cela, aller dans le répertoire /etc/php.d et créer un fichier nommé php-timezone.ini contenant la ligne suivante :

 ::

 date.timezone = Europe/Paris

Après avoir sauvegardé le fichier, n'oubliez pas de redémarrer le service apache de votre serveur.

La base de données MySQL doit être disponible pour pouvoir continuer l'installation (localement ou non). Pour information nous recommandons MariaDB.

************************
Installation de Centreon
************************

Installer un serveur central
----------------------------

Ce chapitre décrit l'installation d'un serveur central Centreon.

Exécutez la commande :

  ::

  $ yum install centreon-base-config-centreon-engine centreon


Suivez la procédure d'installation web :ref:`ici <installation_web_ces>`.

Installer un collecteur
-----------------------

Ce chapitre décrit l'installation d'un collecteur.

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


.. _installation_ppm:

***************************************
Configurez votre supervision facilement
***************************************

En lui-même Centreon est un excellent outil de supervision et peut être
configuré pour correspondre exactement à vos besoins. Cependant vous
trouverez peut-être utile d'utiliser Centreon IMP pour vous aider à
configurer rapidement votre supervision. Centreon IMP vous fournit des
Plugin Packs qui sont des paquets contenant des modèles de configuration
qui réduisent drastiquement le temps nécessaire pour superviser la
plupart des services de votre réseau.

Centreon IMP nécessite les composants techniques Centreon License
Manager et Centreon Plugin Pack Manager pour fonctionner.

Installation système
--------------------

En utilisant CES, l'installation des paquets est très simple. Vous
noterez que Centreon Plugin Pack Manager installe également Centreon
License Manager en tant que dépendance.

::

   $ yum install centreon-pp-manager


Installation web
----------------

Une fois les paquets installés, il est nécessaire d'activer les modules
dans Centreon. Rendez-vous à la page Administration -> Extensions -> Modules.

.. image:: /_static/images/installation/ppm_1.png
   :align: center

Installez tout d'abord Centreon License Manager.

.. image:: /_static/images/installation/ppm_2.png
   :align: center

Puis installez Centreon Plugin Pack Manager.

.. image:: /_static/images/installation/ppm_3.png
   :align: center

Vous pouvez maintenant vous rendre à la page Administration -> Extensions
-> Plugin packs -> Setup. Vous y trouverez vos six premiers Plugin Packs
gratuits pour vous aider à démarrer. Cinq Plugin Packs supplémentaires
sont débloqués après vous être inscrit et plus de 150 sont disponibles
si vous souscrivez à l'offre IMP (plus d'informations sur
`notre site web <https://www.centreon.com>`_).

.. image:: /_static/images/installation/ppm_4.png
   :align: center

Vous pouvez continuer à configurer votre supervision en utilisant
Centreon IMP en suivant :ref:`ce guide <impconfiguration>`.
