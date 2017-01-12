.. _impconfiguration:

=============================================
Configuration simplifiée de Centreon avec IMP
=============================================

Qu'est ce que IMP ? 
-------------------

IMP (Instant Monitoring Platform) est une solution permettant de vous faire gagner beaucoup 
de temps lors de la mise en place de votre supervision. L'objectif est IMP est de simplifier la 
mise en place de vos points de contrôle et également de réduire le temps de maintenance de votre 
plate-forme. Des plugins packagés avec des templates près à l'emploi couplés à une documentation 
de déploiement (monitoring procedure) doit vous permettre de démarrer votre supervision au maximum 
30 minutes après l'installation du système Centreon. Tous ces packs sont maintenus par nos équipes 
et des mises à jour régulières vous permettent d'améliorer de façon continue vos contrôles.

Les plugins sont issus de Centreon Plugins dont l'éventail fonctionnel est un des plus riches du 
marché : 170 environnements couverts soit environ 2000 indicateurs de supervision vous permettent 
de déployer votre supervison rapidement et simplement. 

Vous souhaitez utiliser IMP ? Rien de plus simple. Suivez les instructions ci-dessous.

Pré-requis
----------

**1. Centreon 3.4**

Pour pouvoir utiliser Centreon IMP, vous devez installer la dernière version 3.4 de 
Centreon. Cette version intègre la dernière version de Centreon Web c'est à dire la 
version 2.8.x. 

Cette version 3.4 est installable soit en "fresh install" avec `l'ISO d'installation de Centreon<https://download.centreon.com/>`
soit en mettant à jour votre plateforme Centreon déjà installée. Pour ces deux étapes, 
rendez-vous dans les rubriques appropriées de la documentation.

**2. Une connexion internet**

Votre serveur de supervision Centreon central, doit avoir une connexion à internet. Cette 
dernière permettra de procéder à la récupération des packs via internet afin de 
les installer sur votre système de supervision Centreon.

.. note::
    Si votre serveur de Centreon n'a pas accès directement à internet, pensez à 
    configurer un proxy pour qu'il puisse se connecter à notre site web. Cette fonctionnalité 
    est maintenant disponible depuis la version 2.8.2 de Centreon Web. Si vous n'avez pas cette 
    version, pensez à mettre à jour votre plate-forme.


Connectez votre plate-forme à Centreon 
--------------------------------------

Pour récupérer les plugins packs, votre système Centreon a besoin de se connecter au 
site web Centreon. 

Sans compte Centreon sur le site web, votre serveur pourra récupérer dans un premier temps 6 plugin 
packs. Si vous connectez ensuite votre système au site web Centreon en créant un compte gratuit, vous pourrez 
ensuite en récupérer 6 de plus. En souscrivant à l'offre IMP, vous aurez accès au catalogue 
entier de plugin packs soit à ce jour 170 environnements ce qui représente environ 2000 modèles 
de services près à l'emploi.

+---------------+-----------------+----------------+-------------------+
|               | Without Account | Simple Account | With subscription |
+===============+=================+================+===================+
| 6 base Packs  |        x        |        x       |         x         |
+---------------+-----------------+----------------+-------------------+
| 5 added Packs |                 |        x       |         x         |
+---------------+-----------------+----------------+-------------------+
| +150 Packs    |                 |                |         x         |
+---------------+-----------------+----------------+-------------------+
|         total |    6 packs      |      11 packs  |     +170 Packs    |
+---------------+-----------------+----------------+-------------------+

Quels plugin packs sont disponibles à chaque niveau ?

+------------------------+----------------------------+
| Souscriptions          | Contenu                    |
+========================+============================+
|Plugins packs gratuits  | Cisco standard (SNMP)      |
| -                      | Linux (SNMP)               |
| -                      | MySQL                      |
| -                      | Printer standard (SNMP)    |
| -                      | UPS Standard (SNMP)        |
| -                      | Windows (SNMP)             |
| -                      | Centreon (central)         |
| -                      | Centreon DB                |
| -                      | Centreon Poller            |
| -                      | Centreon Map               |
| -                      | Centreon MBI               |
+------------------------+----------------------------+
|Avec un compte          | DHCP                       |
|utilisateur             | DNS                        |
| -                      | FTP                        |
| -                      | HTTP                       |
| -                      | LDAP                       |
+------------------------+----------------------------+
|Avec une souscription   | Tous les packs du catalogue|
+------------------------+----------------------------+

Le catalogue de plugin packs est disponible `ici<https://documentation-fr.centreon.com/docs/plugins-packs/en/latest/catalog.html>`_.

.. note::
    Nous savons tous que 11 = 6, mais nous préférons ne pas compter les plugins 
    packs servant à superviser votre plateforme de supervision.

Pour connecter votre plate-forme au site web de Centreon, suivez les étapes décrites ci dessous : 


1. Allez créez votre compte Centreon sur le site Centreon
---------------------------------------------------------

TODO : Screenshots

2. Connectez votre plate-forme au site Centreon
-----------------------------------------------

Allez dans Administration -> Extensions -> Subscription et connectez-vous avec 
votre compte Centreon online. Le compte à utiliser sur Centreon est le compte 
que vous avez créé sur le site web de Centreon.

.. image:: /_static/images/configuration/imp3.png
   :align: center

En cliquant sur la flêche a côté du champ "Password", vous allez effecter la 
demande de connexion avec le site Centreon. Pour rappel,  la création d'un compte Centreon sur 
le site web est gratuite et vous donne accès à 11 Plugin Packs.

.. image:: /_static/images/configuration/imp4.png
   :align: center

Vous êtes maintenant connecté ! Vous pouvez maintenant accéder aux 5 nouveaux plugins packs. 

.. note::
    Avec ce compte vous pouvez connecter plusieurs plate-forme à la fois pour faire vos tests.

3. Parcourez le catalogue et installez vos premiers Plugin Packs
----------------------------------------------------------------

Pour installer des plugin packs, cliquez sur le bouton “Setup” pour accéder 
au catalogue ou allez à la page Administration -> Configuration -> Plugin packs
 -> Setup.

.. image:: /_static/images/configuration/imp1.png
   :align: center

Le listing des plugin packs apparait. Seule une partie du catalogue peut être accessible
en fonction de votre subscription. Si votre souscription n’est plus valide ou si vous 
êtes en mode découverte de l’offre Centreon IMP (sans souscription), seule une partie
du catalogue sera accessible. Les éléments non disponible au téléchargement seront 
alors grisés et les boutons d'installation ou de mise à jour seront alors indisponibles.

Vous povez effectuer des recherches grâce aux options à votre disposition :

  * Mot clé (Keyword)
  * Catégorie (Category)
  * Statut (Status)
  * Date de mise à jour du pack (Last update)

Pour accéder à la description d’un pack de supervision cliquer sur son icône.

.. image:: /_static/images/configuration/imp2.png
   :align: center

Pour installer un pack, cliquer sur l’icône d’installation.

.. image:: /_static/images/configuration/imp5.png
   :align: center

Après installation.

.. image:: /_static/images/configuration/imp6.png
   :align: center

N'oubliez pas que chaque pack nécessite également le déploiement de plugins. Dans chaque monitoring 
procédure mise à disposition avec votre pack, vous avez la liste des dépendances sous forme de rpm 
à installer pour que votre supervision puisse démarrer. 

.. note:
    Pensez à déployer tous les rpms sur chaque poller qui le nécessitent. Sinon votre supervision ne 
    pourra pas fonctionner.


3. Maintenant souscrivez !
--------------------------

Vous pouvez souscrire à l'Offre IMP selon différentes formules : sur une durée de 1 mois, 
6 mois ou 1 an. Le tarif est bien évidemment plus attractif en fonction de la durée d'engagement.

Pour souscrire, allez dans Administration -> Extensions -> Subscription et connectez-vous 
avec votre compte Centreon que vous avez péalablement créé sur le site web de Centreon. Procédez au 
paiement de la souscription après avoir séléctionné votre durée de souscription.

Cliquez plutôt sur le bouton "Link".

.. image:: /_static/images/configuration/imp4.png
   :align: center

Vous avez maintenant accès à tous les plugin packs. Vous pouvez installer les packs que vous souhaitez !

Votre abonnement Centreon IMP permet de mettre à jour les packs de supervision en tenant compte des 
évolutions des Systèmes d’Information. Cela peut inclure des améliorations ou de nouveaux ajouts 
fonctionnels.

Si vous décidez d'arrêter votre abonnement Centreon IMP, vous ne pourrez plus avoir accès à ces 
mises à jour et aux améliorations. Les packs installés ne seront pas supprimés et resteront disponibles.

.. note:: 
    Si vous avez des questions ou des problèmes relatives à l'installation de IMP, vous pouvez contacter
    nos équipes techniques via l'adresse email suivant : imp@centreon.com.

