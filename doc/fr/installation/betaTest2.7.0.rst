=============================================
Mise à jour vers la version 2.7.0 de Centreon
=============================================

**********
Pré-requis
**********

Le pré-requis nécessaire au fonctionnement de Centreon 2.7 ont évolué par rapport à précédentes versions. Il est important de suivre les recommandations suivantes pour pouvoir avoir une plate-forme fonctionnelle :

Apache = 2.2
Centreon Engine >= 1.5.0
Centreon Broker >= 2.11.0
CentOS = 6.x ou RedHat >= 6.x
MariaDB = 5.5.35 ou MySQL = 5.1.73
Net-SNMP = 5.5
PHP >= 5.3.0
Qt = 4.7.4
RRDtools = 1.4.7

************************
Procédure de mise à jour
************************

Nous avons recensé ici les différentes étapes nécessaires pour pouvoir passer une plate-forme existante en version 2.7. Il est important de prendre en compte que la version proposée reste une version de validation. Il est vivement recommandé de ne pas installer une version RC de Centreon 2.7 en production.

.. note::
	Attention : cette procédure est réalisée dans le contexte d’une CES. Toutes les commandes et les mises à jours seront basées sur de l’environnement CentOS / RedHat et yum.


Mise en place du repo testing
=============================

Nous avons mis en place un repository yum testing dans lequel nous avons mis les RPM de Centreon 2.7, Centreon Engine 1.5 et Centreon Broker 2.11. Une série de widgets est également disponible dans ce repo.
Pour mettre en place votre fichier repo spécifique testing lancez les commandes suivantes : 

# cd /etc/yum.repos.d
# wget http://yum.centreon.com/standard/3.0/testing/ces-standard-testing.repo -O /etc/yum.repos.d/ces-standard-testing.repo

Arrêt des instances de collecte
===============================

Avant de commencer la mise à jour, assurez vous de ne pas avoir de fichier de rétention 
actif pour Centreon-Broker.

Stoppez Centreon Broker et Centreon Engine sur l’ensemble des pollers

# /etc/init.d/centengine stop
# /etc/init.d/cbd stop

Mise à jour l’ensemble des paquets
====================================

# yum update centreon

Redémarrez le serveur Apache 
============================

Suite à l’installation de PHP-intl, il est nécessaire de redémarrer le serveur apache afin de prendre en compte la nouvelle extension.

# /etc/init.d/httpd restart

Réalisez la mise à jour Web de Centreon 2.7.0
=============================================

Suivez le wizard de mise à jour Web afin de terminer les mises à jours pour les modifications au niveau de la base SQL soient appliquées. Durant cette phase, un nouveau fichier de configuration va être également créé.

Exportez la configuration vers l’ensemble des pollers
=====================================================

Pour terminer l’installation, il est nécessaire de générer une première fois les configurations de Centreon Engine et Centreon Broker. Pour cela, allez dans Configuration > Poller et cliquer sur l’icone de génération (attention la page de génération a été supprimée).
 
Redémarrez les moteurs Centreon Engine et Centreon Broker sur l’ensemble des pollers
====================================================================================

Vous pouvez maintenant redémarrer les instances de collecte afin de remettre le service en place. Pour ceci, lancez les commandes suivantes : 

# /etc/init.d/centengine start
# /etc/init.d/cbd start

*********************************************
Les risques identifiés lors de la mise à jour
*********************************************

Afin de vous aider à éviter le plus possible des problèmes éventuels liés à la mise à jour de votre plate-forme en version 2.7 de Centreon couplée à la version 1.5 de Engine et 2.11 de Broker, nous souhaitons vous partager la liste des risques potentiels suite à cette action. Cela ne veut pas dire que vous rencontrerez ces problèmes lors de la mise à jour. Cependant, ce sont des points que nous vous incitons à surveiller après la mise à jour. Cette liste de risque nous aidera je l’espère valider que tout se passe bien de votre côté.

Les risques sont les suivants : 
===============================

* Problèmes de dépendances avec Centreon Engine et Centreon Broker : les deux dernières versions (Centreon Broker 2.11.0 et Centreon Engine 1.5.0) sont des pré-requis au fonctionnement de Centreon 2.7.0. 
* Problèmes de mise à jour des schémas de base de données
* Passage de toutes les tables MySQL en InnoDB (sauf logs et data_bin qui ne seront pas migrées automatiquement)
* Changement au niveau de la table hostgroup et servicegroup dans la base storage
* Les temporaries et les failovers sont définis par défaut sur Centreon Broker : Il est donc possible que cela entre en conflit avec la configuration existant avant la mise à jour. Bien vérifier après la mise à jour qu’il ne reste pas des anciens fichiers et que cela n’a pas bloqué le broker générant ainsi des pertes de données
* Problème de cache navigateur : le cache du navigateur doit être vidée à la fin de la mise à jour et web et également après la première connexion.		
* Problème avec des dépendances php (intl) : un nouveau pré-requis PHP a été ajouté. Suite à la mise à jour RPM, il est nécessaire de redémarrer Apache pour que celui-ci soit chargé.
* Problème de compatibilité avec des modules installés : le style de la 2.7.0 change complètement des versions précédentes. Les modules Centreon doivent donc être adaptés en conséquence. Ne migrez pas si vos modules ne sont pas compatibles.
* Génération de conf qui ne se génère pas normalement : le moteur de génération de la configuration a été réécrit. Il y a donc un risque d’erreurs dans les configurations exportées.
* Bascule direct de NDOutils vers Centreon Broker au passage de la version 2.7 : Centreon 2.7.0 n’est plus compatible avec Nagios et NDOutils. Des problèmes surviendront en cas de tentative de mise à jour d’une machine fonctionnant avec Nagios/NDOutils vers la version 2.7.0.

C'est parti !
=============

Pour nous faire part de vos retours, merci de faire cela sur notre `github <https://github.com/centreon/centreon>`_. Dans le but de bien catégoriser les tickets remontés par cette campagne de beta test, nous avons mis en place un tag specifique nommé "BetaTest". Merci d'ajouter ce tag aux tickets dès que vous découvrirez un problème.

Nous restons à votre disposition si vous avez des besoins ou des questions. Nous restons disponible à l'adresse suivante : centreon-beta-test@centreon.com
