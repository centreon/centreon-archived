.. _betaTest2_8_0: 

=================================================
Mise à jour vers la version 2.8.0 de Centreon web
=================================================

**********
Pré-requis
**********

Le pré-requis nécessaire au fonctionnement de Centreon web 2.! ont évolué par 
rapport à précédentes versions. Il est important de suivre les recommandations 
suivantes pour pouvoir avoir une plate-forme fonctionnelle :

* Apache = 2.2
* Centreon Engine >= 1.6.0
* Centreon Broker >= 3.0
* CentOS = 6.x ou RedHat >= 6.x
* MariaDB = 5.5.47 ou MySQL = 5.1.73
* Net-SNMP = 5.5
* PHP >= 5.3.0
* Qt = 4.7.4
* RRDtools = 1.4.7

************************
Procédure de mise à jour
************************

Nous avons recensé ici les différentes étapes nécessaires pour pouvoir passer une 
plate-forme existante en version Centreon web 2.8. **Il est important de prendre en 
compte que la version proposée reste une version bétâ**. **Il est vivement recommandé 
de ne pas installer une version RC de Centreon 2.7 en production.**

.. note::
	Attention : cette procédure est réalisée dans le contexte de Centreon ISO. Toutes les commandes et les mises à jours seront basées sur de l’environnement CentOS / RedHat et yum.


Mise en place du repo testing
=============================

Nous avons mis en place un repository yum testing dans lequel nous avons mis les RPM 
de Centreon web 2.8.0, Centreon Engine 1.6 et Centreon Broker 3.0. Une série de widgets 
est également disponible dans ce repo. Pour mettre en place votre fichier repo spécifique 
testing lancez les commandes suivantes : 

    # cd /etc/yum.repos.d
    # wget http://yum.centreon.com/standard/3.4/el6/testing/centreon-testing.repo -O /etc/yum.repos.d/ces-standard-testing.repo

Arrêt des instances de collecte
===============================

Avant de commencer la mise à jour, assurez vous de ne pas avoir de fichier de rétention 
actif pour Centreon-Broker.

Stoppez Centreon Broker et Centreon Engine sur l’ensemble des pollers

::

    # /etc/init.d/centengine stop
    # /etc/init.d/cbd stop

Mise à jour l’ensemble des paquets
====================================

::

    # yum update centreon

Redémarrez le serveur Apache 
============================

Suite à l’installation de PHP-intl, il est nécessaire de redémarrer le serveur 
apache afin de prendre en compte la nouvelle extension.

::

    # /etc/init.d/httpd restart

Réalisez la mise à jour Web de Centreon 2.8.0
=============================================

Suivez le wizard de mise à jour Web afin de terminer les mises à jours pour les 
modifications au niveau de la base SQL soient appliquées. Durant cette phase, 
un nouveau fichier de configuration va être également créé.

Exportez la configuration vers l’ensemble des pollers
=====================================================

Pour terminer l’installation, il est nécessaire de générer une première fois les 
configurations de Centreon Engine et Centreon Broker. Pour cela, allez dans le menu
**Configuration > Poller**, sélectionnez l'ensemble des poller et cliquer sur le
bouton "Apply configuration**
 
Redémarrez les moteurs Centreon Engine et Centreon Broker sur l’ensemble des pollers
====================================================================================

Vous pouvez maintenant redémarrer les instances de collecte afin de remettre le service en place. Pour ceci, lancez les commandes suivantes : 

::

    # /etc/init.d/centengine start
    # /etc/init.d/cbd start

*********************************************
Les risques identifiés lors de la mise à jour
*********************************************

Afin de vous aider à éviter le plus possible des problèmes éventuels liés à la 
mise à jour de votre plate-forme en version 2.8 de Centreon Web couplée à la 
version 1.6 de Centreon Engine et 3.0 de Centreon Broker, nous souhaitons vous 
partager la liste des risques potentiels suite à cette action. Cela ne veut pas 
dire que vous rencontrerez ces problèmes lors de la mise à jour. Cependant, ce 
sont des points que nous vous incitons à surveiller après la mise à jour. Cette 
liste de risque nous aidera je l’espère valider que tout se passe bien de votre côté.

Les risques sont les suivants : 
===============================

* Les nouveau graphiques de performances ont des échelles affichant trop de détails
* Des erreurs PHP de type warning apparaissent dans le journal d'évènement d'Apache
* Des incompatibilités avec les modules Centreon déjà installés peuvent apparaitre


C'est parti !
=============

Pour nous faire part de vos retours, merci de faire cela sur notre `github <https://github.com/centreon/centreon>`_. Dans le but de bien catégoriser les tickets remontés par cette campagne de beta test, nous avons mis en place un tag specifique nommé "BetaTest". Merci d'ajouter ce tag aux tickets dès que vous découvrirez un problème.

Nous restons à votre disposition si vous avez des besoins ou des questions. Nous restons disponible à l'adresse suivante : centreon-beta-test@centreon.com

