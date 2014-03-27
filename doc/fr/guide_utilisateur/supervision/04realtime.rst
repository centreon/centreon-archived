.. _realtime_monitoring:

======================
Supervision Temps-réel
======================

Le menu **Supervision** permet de visualiser en temps-réel l'évolution de la supervision de son système d'information.

*****************
Statut des objets
*****************

Les statuts sont des indicateurs pour les hôtes ou les services. Chaque statut a une signification bien précise pour l'objet.
A chaque statut correspond un code généré par la sonde de supervision en fonction des seuils définis par l'utilisateur.

Statut des hôtes
================

Le tableau ci-dessous résume l'ensemble des statuts possibles pour un hôte.

+-------------------+----------------------+------------------------------------+
| Statut            |  Code de retour      | Description                        | 
+===================+======================+====================================+
| UP                |  0                   | L'hôte est disponible et joignable	|
+-------------------+----------------------+------------------------------------+
| DOWN              |  1                   | L'hôte est indisponible            |
+-------------------+----------------------+------------------------------------+
| UNREACHABLE       |  2                   | L'hôte est injoignable             |
+-------------------+----------------------+------------------------------------+

Statut des services
===================
 
Le tableau ci-dessous résume l'ensemble des statuts possibles pour un service.

+-------------------+----------------------+---------------------------------------------------------------------------+
| Statut            |  Code de retour      | Description                                                               | 
+===================+======================+===========================================================================+
| OK                |  0                   | Le service ne présente aucun problème                                     |
+-------------------+----------------------+---------------------------------------------------------------------------+
| WARNING           |  1                   | Le service a dépassé le seuil d'alerte                                    |
+-------------------+----------------------+---------------------------------------------------------------------------+
| CRITICAL          |  2                   | Le service a dépassé le seuil critique                                    |
+-------------------+----------------------+---------------------------------------------------------------------------+
| UNKNOWN           |  3                   | Le statut du service ne peut être vérifié (exemple : agent SNMP DOWN...)  |
+-------------------+----------------------+---------------------------------------------------------------------------+

Statuts avancés
===============

En plus des statuts standards, de nouveaux statuts  permettent d'ajouter des informations complémentaires :

* Le statut PENDING est un statut affiché pour un service ou un hôte fraîchement configuré mais qui n'a pas encore été contrôlé par l'ordonnanceur.
* Le statut UNREACHABLE est un statut indiquant que l'hôte est situé (relation de parenté) en aval d'un hôte dans un statut DOWN.
* Le statut FLAPPING (bagotant) est un statut indiquant que le pourcentage de changement de statut de l'objet est très élevé. Ce pourcentage est obtenu à partir de calculs effectués par le moteur de supervision.
* Le statut ACKNOWLEDGED est un statut indiquant que l'incident du service ou de l'hôte est pris en compte par un utilisateur.
* Le statut DOWNTIME est un statut indiquant que l'incident du service ou de l'hôte est survenu durant une période de temps d'arrêt programmé.

******************
Etats SOFT et HARD
******************

Un hôte ou un service peut avoir deux états :

* SOFT : Signifie qu'un incident vient d'être détecté et que ce dernier doit être confirmé.
* HARD : Signifie que le statut de l'incident est confirmé. Lorsque le statut est confirmé, le processus de notification est enclenché (envoi d'un mail, SMS, ...).

Confirmation d'un statut
========================

Un incident (statut non-OK) est confirmé à partir du moment où le nombre d'essai de validation est arrivé à son terme.
La configuration d'un objet (hôte ou service) implique un intervalle de contrôle régulier, un nombre d'essai pour valider un état non-OK ainsi qu'un intervalle non-régulier de contrôle.
Dès la détection du premier incident, le statut est dans un état "SOFT" jusqu'à sa validation en état "HARD" déclenchant le processus de notification.

Exemple :

Un service a les paramètres de vérifications suivants :

 * Nombre de contrôles avant validation de l'état : 3
 * Intervalle normal de contrôle : 5 minutes
 * Intervalle non-régulier de contrôle : 1 minute
 
Imaginons le scénario suivant :

 * Instant t + 0 : Le service est vérifié, il a le statut OK.
 * Instant t + 5 : La seconde vérification montre que le service a le statut CRITICAL. Le service passe en état SOFT (essai 1/3).
 * Instant t + 6 : La troisième vérification a lieu, le service a toujours le statut CRITICAL en état SOFT (essai 2/3).
 * Instant t + 7 : La quatrième vérification montre que le service a toujours le statut CRITICAL (essai 3/3). Le nombre d'essais a été atteint, le statut est configuré (état HARD). Le processus de notification est enclenché.
 * Instant t + 8 : Le service retrouve le statut OK. Il passe directement en état HARD. Le processus de notification est enclenché.
 * Instant t + 13 : Le service a le statut WARNING. Il passe en état SOFT (essai 1/3).
 * Instant t + 14 : Le service a toujours le statut WARNING (essai 2/3).
 * Instant t + 15 : Le service a le statut CRITICAL. Il reste en état SOFT car il a changé de statut.

******************
Actions génériques
******************

Par défaut, lors de la visualisation des statuts des hôtes ou des services, les données de supervision sont rafraîchies automatiquement (15 secondes par défaut).
Cependant, plusieurs icônes permettent de contrôler le rafraichissement des données.
Le tableau ci-dessous résume les différentes fonctions de ces icônes :

+------------+-----------------------------------------------------------------------+
|   Icône    |   Description                                                         | 
+============+=======================================================================+
| |refresh|  | Permet de rafraîchir manuellement les résultats                       |
+------------+-----------------------------------------------------------------------+
| |pause|    | Permet de mettre en pause le rafraîchissement automatique des données |
+------------+-----------------------------------------------------------------------+
| |resume|   | Permet de reprendre le rafraîchissement automatique des données       |
+------------+-----------------------------------------------------------------------+

*****
Hôtes
*****

Visualisation
=============

Pour visualiser le statut des hôtes, rendez-vous dans le menu **Supervision** ==> **Hôtes**.

.. image :: images/01.png
   :align: center

La barre de recherche grise permet de filtrer les résultats affichés.
Le menu de gauche permet de modifier les hôtes visibles au sein du tableau :

* Pour visualiser les hôtes rencontrant un problème mais étant non acquittés, cliquez sur **Problèmes non acquittés**
* Pour visualiser tous les hôtes rencontrant un problème, cliquez sur **Problèmes en cours**
* Pour visualiser tous les hôtes, cliquez sur **Hôtes**
* Pour visualiser les hôtes classés par groupes d'hôtes, cliquez sur **Groupes d'hôtes**

.. image :: /images/guide_utilisateur/supervision/04hostgroup.png
   :align: center 

Tableaux d'hôtes
================

Le tableau ci-dessous donne une description de toutes les colonnes du tableau affiché lors de la visualisation des hôtes :

+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
|   Nom de la colonne      |   Description                                                                                                                    | 
+==========================+==================================================================================================================================+
| S                        | Affiche le niveau de criticité de l'hôte                                                                                         |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Hôtes                    | Affiche le nom de l'hôte.                                                                                                        |
|                          | L'icône |nonotifications| indique que les notifications pour cet hôte sont désactivées.                                          |
|                          | L'icône |graphperformances| permet de visualiser l'ensemble des graphiques de performances pour cet hôte                         |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Statut                   | Permet de visualiser le statut de l'hôte                                                                                         |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Adresse IP               | Indique l'adresse IP de l'hôte                                                                                                   |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Dernier contrôle         | Affiche la date et l'heure du dernier contrôle                                                                                   |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Durée                    | Affiche la durée depuis laquelle l'hôte a conservé son statut actuel                                                             |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Validé depuis            | Affiche la durée depuis laquelle l'hôte a conservé son statut actuel (n'apparait pas lors de la visualisation de tous les hôtes) |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Tentatives               | Affiche le nombre de tentatives effectuées avant de valider l'état                                                               |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+
| Statut détaillé          | Affiche le message expliquant le statut de l'hôte                                                                                |
+--------------------------+----------------------------------------------------------------------------------------------------------------------------------+

.. note::
    La colonne criticité ainsi que le filtre associé apparaissent si au moins un objet affiché possède un niveau de criticité.

Filtres disponibles
-------------------

Vous pouvez filtrer le résultat présenté via les filtres suivants :

* **Hôte** : permet de filtrer par nom d'hôte via une recherche de type SQL LIKE.
* **Statut** : permet de filtrer sur le statut des hôtes.
* **Criticité** : permet de filtrer par criticité.
* **Collecteur** : permet de filtrer les hôtes par collecteur. Seuls les hôtes du collecteur sélectionné seront affichés.
* **Groupe d'hôte** : permet de filtrer par groupe d'hôte. Seuls les hôtes du groupe d'hôtes sélectionné seront affichés.

.. note::
    La recherche sur les champs texte ne commence qu'à partir de la saisie d'au moins 3 caractères.

Tableau de groupes d'hôtes
==========================

Le tableau ci-dessous donne une description de toutes les colonnes du tableau affiché lors de la visualisation des groupes d'hôtes :

+--------------------------+------------------------------------------------------------------------------------------------------------+
|   Nom de la colonne      |   Description                                                                                              | 
+==========================+============================================================================================================+
| Groupes d'hôtes          | Liste l'ensemble des groupes d'hôtes                                                                       |
+--------------------------+------------------------------------------------------------------------------------------------------------+
| Etat des hôtes           | Permet de visualiser le nombre d'hôtes ayant le statut disponible, indisponible, injoignable ou en attente |
+--------------------------+------------------------------------------------------------------------------------------------------------+
| Etat des services        | Permet de visualiser le nombre de services ayant le statut OK, WARNING, CRITICAL ou PENDING                |
+--------------------------+------------------------------------------------------------------------------------------------------------+

Filtres disponibles
-------------------

Vous pouvez filtrer le résultat présenté en sélectionnant dans la liste déroulante un collecteur.
Seuls les hôtes du collecteur sélectionné seront affichés.

Détails d'un hôte
=================

Lorsque vous cliquez sur un hôte, la page suivante s'affiche :

.. image :: /images/guide_utilisateur/supervision/04hostdetail.png
   :align: center 

Détails du statut
-----------------

Le tableau ci-dessous résume l'ensemble des attributs de cette partie :

+------------------------------------------+-----------------------------------------------------------------------------------------------------+
|   Attributs                              |   Description                                                                                       | 
+==========================================+=====================================================================================================+
| Statut de l'hôte                         | Affiche le statut de l'hôte                                                                         |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Statut détaillé                          | Affiche le message associé au statut de l'hôte                                                      |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Données de performance                   | Affiche les données de performances renvoyées par la sonde                                          |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Tentative                                | Affiche le nombre de tentative avant validation de l'état                                           |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Type d'état                              | Affiche le type d'état ('SOFT' ou 'HARD')                                                           |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernier contrôle                         | Affiche la date et l'heure du dernier contrôle effectué sur l'hôte                                  |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Prochain contrôle                        | Affiche la date et l'heure du prochain contrôle effectué sur l'hôte                                 |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Latence                                  | Affiche le temps de latence entre la programmation de l'exécution et l'exécution réelle de la sonde |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Temps d'exécution                        | Affiche le temps d'éxécution de la sonde                                                            |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Changement du dernier état               | Affiche la date et l'heure depuis laquelle l'hôte est dans l'état actuel                            |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Durée de l'état actuel                   | Affiche la durée depuis laquelle l'hôte est dans l'état actuel                                      |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernier notification                     | Affiche la date et l'heure d'envoi de la dernière notification                                      |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Prochaine notification                   | Affiche la date et l'heure d'envoi de la prochaine notification                                     |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Numéro de notification actuel            | Affiche le nombre de notifications déjà envoyées                                                    |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Est\-ce que le statut de l'hôte bagote ? | Indique si l'hôte bagotte (a le statut FLAPPING)                                                    |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Pourcentage de changement de statut      | Affiche le pourcentage de changement d'état                                                         |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Planification d'arrêt en cours?          | Indique si l'hôte est concerné par un temps d'arrêt                                                 |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernière mise à jour                     | Affiche la date et l'heure de la dernière mise à jour                                               |
+------------------------------------------+-----------------------------------------------------------------------------------------------------+

Options et Commandes disponibles
--------------------------------

Les options ainsi que les commandes permettent d'effectuer un certain nombre d'actions sur l'hôte.
Ces différentes options sont traitées au sein du :ref:`guide d'exploitation<exploitationguide>`.

.. _shorthostlinks:

Raccourcis d'hôtes
------------------

Le tableau ci-dessous résume la signification des icônes :
 
+------------------------+--------------------------------------------------------------------+
|  Icône                 |  Description                                                       | 
+========================+====================================================================+
| |configure|            | Redirige vers la page de configuration de l'hôte                   |
+------------------------+--------------------------------------------------------------------+
| |showservicesstatuts|  | Affiche le statut de tous les services liés à l'hôte               |
+------------------------+--------------------------------------------------------------------+
| |showlogs|             | Affiche les journaux liés à l'hôte                                 |
+------------------------+--------------------------------------------------------------------+
| |showresult|           | Affiche le rapport de disponibilité lié à l'hôte                   |
+------------------------+--------------------------------------------------------------------+
| |showgraphperf|        | Affiche les graphiques de performances des services liés à l'hôte  |
+------------------------+--------------------------------------------------------------------+

Outils
------

Le conteneur **Outils** permet :

* D'effectuer un PING vers l'hôte
* D'effectuer un traceroute vers l'hôte

Liens
-----

Le conteneur **Liens** permet de visualiser les groupes d'hôtes auxquels l'hôte appartient.

Notifications
-------------

Le conteneur **Notifications** permet de visualiser quels sont les contacts et les groupes de contacts qui seront alertés
en cas d'envoi d'une notification.

********
Services
********

Visualisation
=============

Pour visualiser le statut des services, rendez-vous dans le menu **Supervision** ==> **Services**.

.. image :: /images/guide_utilisateur/supervision/04servicelist.png
   :align: center 

La barre de recherche grise permet de filtrer les résultats affichés.
Le menu de gauche permet de modifier les services visibles au sein du tableau :

* Pour visualiser les services rencontrant un problème validé (état "HARD") mais étant non acquittés, cliquez sur **Problèmes non acquittés**
* Pour visualiser tous les services rencontrant un problème (validé ou non, acquittés ou non), cliquez sur **Problèmes en cours**
* Pour visualiser tous les services, cliquez sur **Tous les services**
* Pour visualiser tous les services (classés par hôtes), quelque soit le statut, cliquez sur **Détails** (en dessous d'hôtes)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyhostdetail.png
   :align: center
   
* Pour visualiser le nombre de services (classés par hôtes et statuts), cliquez sur **Résumé** (en dessous d'hôtes)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyhost.png
   :align: center
  
* Pour visualiser tous les services (classés par groupes d'hôtes), cliquez sur **Détails** (en dessous de groupe d'hôtes)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyhostgroupdetail.png
   :align: center
   
* Pour visualiser le nombre de services (classés par groupes d'hôtes et statuts), cliquez sur **Résumé** (en dessous de groupe d'hôtes)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyhostgroup.png
   :align: center
   
* Pour visualiser tous les services (classés par groupes de services), cliquez sur **Détails** (en dessous de groupe de services)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyservicegroupdetail.png
   :align: center
   
* Pour visualiser le nombre de services (classés par groupes de services et statuts), cliquez sur **Résumé** (en dessous de groupe de services)

.. image :: /images/guide_utilisateur/supervision/04servicelistbyservicegroup.png
   :align: center

* Pour visualiser les méta-services, cliquez sur **Méta-Services**

.. image :: /images/guide_utilisateur/supervision/04metaservices.png
   :align: center

Tableaux de services
====================

Le tableau ci-dessous décrit les colonnes affichées lors de la visualisation des services.

+--------------------+-------------------------------------------------------------------------------------------------------------------------+
|  Nom de la colonne |   Description                                                                                                           | 
+====================+=========================================================================================================================+
| S                  | Affiche le niveau de criticité du service                                                                               |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Hôtes              | Affiche le nom de l'hôte. L'icône |url_link| permet d'accéder à une page web décrivant l'hôte                           |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Services           | Affiche le nom du service. L'icône |nonotifications| indique que les notifications pour ce service sont désactivées.    |
|                    | L'icône |graphperformances| permet de visualiser le graphique de performance lié à ce service.                          |
|                    | L'icône |url_link| permet d'accéder à une page web décrivant le service                                                 |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Validé depuis      | Affiche la durée depuis laquelle le service a conservé son statut actuel                                                |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Dernier contrôle   | Affiche la date et l'heure du dernier contrôle effectué                                                                 |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Tentatives         | Affiche le nombre de tentatives effectuées pour valider l'état                                                          |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+
| Statut détaillé    | Affiche le message expliquant le statut du service                                                                      |
+--------------------+-------------------------------------------------------------------------------------------------------------------------+

.. note::
    La colonne criticité ainsi que le filtre associé apparaissent si au moins un objet affiché possède un niveau de criticité.

.. note::
    La colonne **Validé depuis** n'apparait pas lors de la sélection du menu contextuel **Tous les services**.

Tableaux des groupes
====================

Le tableau ci-dessous décrit les colonnes affichées lors de la visualisation des services classés par groupes.

+------------------------------+--------------------------------------------------------------------------------------------------------------------------------------+
|   Nom de la colonne          |   Description                                                                                                                        | 
+==============================+======================================================================================================================================+
| Hôtes ou Groupes d'hôtes     | Liste l'ensemble des hôtes ou hôtes séparés par des groupes d'hôtes ou hôtes séparées par des groupes de services                    |
| Hôtes ou Groupes de services | L'icône |showservices| permet de visualiser l'ensemble des services liés à l'hôte                                                    |
| Hôtes                        | L'icône |graphperformances| permet de visualiser l'ensemble des graphiques de performances liés aux services appartenant à l'hôte    |
+------------------------------+--------------------------------------------------------------------------------------------------------------------------------------+
| Statut                       | Affiche le statut de l'hôte                                                                                                          |
+------------------------------+--------------------------------------------------------------------------------------------------------------------------------------+
| Informations sur les services| Affiche le statut des services (Mode détaillé) ou le nombre de services classés par statut (Mode résumé)                             |
+------------------------------+--------------------------------------------------------------------------------------------------------------------------------------+

Tableaux des méta-services
==========================

Le tableau ci-dessous décrit les colonnes affichées lors de la visualisation des méta-services.

+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
|   Nom de la colonne      |   Description                                                                                                                            | 
+==========================+==========================================================================================================================================+
| Méta\-Services           | Affiche le nom du méta\-service. L'icône |graphperformances| permet de visualiser le graphique de performance lié à ce méta\-service.    |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
| Statut                   | Affiche le statut du méta\-service                                                                                                       |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
| Durée                    | Affiche la durée depuis laquelle le méta\-service n'a pas changé de statut                                                               |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
| Dernier contrôle         | Affiche la date et l'heure du dernier contrôle                                                                                           |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
| Tentative                | Affiche le nombre de tentatives pour valider l'état                                                                                      |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+
| Statut détaillé          | Affiche le message lié au statut                                                                                                         |
+--------------------------+------------------------------------------------------------------------------------------------------------------------------------------+

Détails d'un service
====================

Lorsque vous cliquez sur un service, la page suivante s'affiche :

.. image :: /images/guide_utilisateur/supervision/04servicedetail.png
   :align: center

Détails du statut
-----------------

Le tableau ci-dessous résume l'ensemble des attributs de cette partie :

+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
|   Attributs                               |   Description                                                                                       |
+===========================================+=====================================================================================================+
| Statut du service                         | Affiche le statut du service                                                                        |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Statut détaillé                           | Affiche le message associé au statut du service                                                     |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Informations d'état étendues              | Affiche le message long ("long output") associé au statut du service                                |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Données de performance                    | Affiche les données de performances renvoyées par la sonde                                          |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Tentative                                 | Affiche le nombre de tentative en cours pour valider l'état                                         |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Type d'état                               | Affiche le type d'état ('SOFT' ou 'HARD')                                                           |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernier contrôle                          | Affiche la date et l'heure du dernier contrôle effectué sur le service                              |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Prochain contrôle                         | Affiche la date et l'heure du prochain contrôle effectué sur le service                             |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Latence                                   | Affiche le temps de latence entre la programmation de l'exécution et son exécution réelle           |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Temps d'exécution                         | Affiche le temps d'éxécution de la sonde                                                            |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Changement du dernier état                | Affiche la date et l'heure depuis laquelle le service est dans l'état actuel                        |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Durée de l'état actuel                    | Affiche la durée depuis laquelle le service est dans l'état actuel                                  |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernier notification                      | Affiche la date et l'heure d'envoi de la dernière notification                                      |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Numéro de notification actuel             | Affiche le nombre de notifications déjà envoyées                                                    |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Est\-ce que le statut du service bagote ? | Indique si le service bagotte (statut FLAPPING)                                                     |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Pourcentage de changement de statut       | Affiche le pourcentage de changement d'état                                                         |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Planification d'arrêt en cours?           | Indique si le service est concerné par un temps d'arrêt                                             |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+
| Dernière mise à jour                      | Affiche la date et l'heure de la dernière mise à jour                                               |
+-------------------------------------------+-----------------------------------------------------------------------------------------------------+

Options et commandes du service
-------------------------------

Les options ainsi que les commandes du service permettent d'effectuer un certain nombre d'actions sur le service.
Ces différentes options sont traitées au sein du :ref:`guide d'exploitation<exploitationguide>`.

Graphique détaillé et graphiques des statuts
--------------------------------------------

Les parties **Graphique détaillé** et **Graphique des statuts** permettent respectivement de visualiser le graphique de performance 
ainsi que le graphique d'historique de statuts pour ce service.

Raccourcis d'hôte
-----------------

Les raccourcis d'hôtes sont les mêmes que ceux de la fiche :ref:`d'hôte<shorthostlinks>`.

Raccourcis de service
---------------------

Le tableau ci-dessous résume la signification des icônes :
 
+------------------------+--------------------------------------------------------------------+
|  Icône                 |  Description                                                       | 
+========================+====================================================================+
| |configure|            | Redirige vers la page de configuration du service                  |
+------------------------+--------------------------------------------------------------------+
| |showservicesstatuts|  | Affiche le statut de tous les services liés à l'hôte               |
+------------------------+--------------------------------------------------------------------+
| |showlogs|             | Affiche les journaux liés au service                               |
+------------------------+--------------------------------------------------------------------+
| |showresult|           | Affiche le rapport de disponibilité lié au service                 |
+------------------------+--------------------------------------------------------------------+

Liens
-----

Le conteneur **Liens** permet de visualiser :

* Les groupes d'hôtes auxquels l'hôte contenant le service appartient
* Les groupes de services auxquels le service appartient
* Les catégories de services auxquels le service appartient

Notifications
-------------

Le conteneur **Notifications** permet de visualiser quels sont les contacts et les groupes de contacts qui seront alertés
en cas d'envoi d'une notification.

**********************
Moteurs de supervision
**********************

Ce menu contextuel permet de visualiser des informations complémentaires telles que la file d'attente des contrôles prévus 
par l'ordonnanceur, les commentaires ou les temps d'arrêt ajoutés aux objets .

.. note::
	Pour plus d'informations sur les commentaires ou sur les les temps d'arrêt, rendez-vous dans le :ref:`guide d'exploitation<exploitationguide>`.

File d'attente
==============

La file d'attente présente l'ordonnancement prévu des contrôles à réaliser par les ordonnanceurs de supervision.

Pour visualiser la file d'attente :

#. Rendez-vous dans le menu **Supervision  ==> Hôtes** ou **Services**
#. Dans le menu de gauche, sous **Moteur de supervision**, cliquez sur **File d'attente**

.. image :: /images/guide_utilisateur/supervision/04waitingqueue.png
   :align: center

Le tableau ci-dessous décrit les colonnes de cette page.

+--------------------+-------------------------------------------------+
|  Nom de la colonne |   Description                                   |
+====================+=================================================+
| Hôtes              | Indique le nom de l'hôte                        |
+--------------------+-------------------------------------------------+
| Services           | Indique le nom du service                       |
+--------------------+-------------------------------------------------+
| Dernier contrôle   | Affiche la date et l'heure du dernier contrôle  |
+--------------------+-------------------------------------------------+
| Prochain contrôle  | Affiche la date et l'heure du prochain contrôle |
+--------------------+-------------------------------------------------+
| Contrôle actif     | Indique si le contrôle est actif et/ou passif   |
+--------------------+-------------------------------------------------+

Filtres disponibles
-------------------

Vous pouvez filtrer le résultat présenté via les filtres suivants :

* **Hôte** : permet de filtrer par nom d'hôte via une recherche de type SQL LIKE.
* **Service** : permet de filtrer par le nom du service.
* **Collecteur** : permet de filtrer par ordonnanceur. Seules les ressources supervisées par cet ordonnanceur seront affichées.

.. note::
    La recherche sur les champs texte ne commence qu'à partir de la saisie du troisième caractère.

Les temps d'arrêts
==================

Pour visualiser les temps d'arrêts en cours sur les ressources :

#. Rendez-vous dans le menu **Supervision** ==> **Hôtes** ou **Services**
#. Dans le menu de gauche, sous **Moteur de supervision**, cliquez sur **Temps d'arrêt**

.. image :: /images/guide_utilisateur/supervision/04downtimelist.png
   :align: center

Le tableau ci-dessous décrit les colonnes de cette page.

+------------------------------------------------+---------------------------------------------------+
|  Nom de la colonne                             |   Description                                     | 
+================================================+===================================================+
| Nom de l'hôte                                  | Indique le nom de l'hôte                          |
+------------------------------------------------+---------------------------------------------------+
| Service (si on utilise la page Services)       | Affiche le service concerné par le temps d'arrêt  |
+------------------------------------------------+---------------------------------------------------+
| Date et heure de début et Date et heure de fin | Affiche la date et l'heure de début et de fin     |
+------------------------------------------------+---------------------------------------------------+
| Durée                                          | Affiche la durée du temps d'arrêt                 |
+------------------------------------------------+---------------------------------------------------+
| Auteur                                         | Affiche la personne ayant ajouté ce temps d'arrêt |
+------------------------------------------------+---------------------------------------------------+
| Commentaires                                   | Affiche le raison du temps d'arrêt                |
+------------------------------------------------+---------------------------------------------------+
| Démarré                                        | Indique si le temps d'arrêt est en cours ou non   |
+------------------------------------------------+---------------------------------------------------+
| Fixe                                           | Indique si le temps d'arrêt est fixe ou non       |
+------------------------------------------------+---------------------------------------------------+

Filtres disponibles
-------------------

Vous pouvez filtrer le résultat présenté via les filtres suivants :

* **Nom de l'hôte** : permet de filtrer par nom d'hôte via une recherche de type SQL LIKE.
* **Service** : permet de filtrer par le nom du service.
* **Statut détaillé** : permet de filtrer par le statut détaillé des services.
* **Auteur** : permet de filtrer par utilisateur ayant créé des commentaires.
* **Afficher les temps d'arrêt terminés** : permet d'afficher en plus les temps d'arrêt terminés.
* **Afficher les temps d'arrêt récurrents** : permet d'afficher les temps d'arrêts récurrents.

.. note::
    La recherche sur les champs texte ne commence qu'à partir de la saisie du troisième caractère.

Les commentaires
================

Pour visualiser les commentaires définis sur les ressources :

#. Rendez-vous dans le menu **Supervision** ==> **Hôtes** ou **Services**
#. Dans le menu de gauche, sous **Moteur de supervision** cliquez sur **Commentaires**

.. image :: /images/guide_utilisateur/supervision/04comments.png
   :align: center

Le tableau ci-dessous décrit les colonnes de cette page.

+-------------------------------------------------------------------+------------------------------------------------------------------------+
|  Nom de la colonne                                                |   Description                                                          | 
+===================================================================+========================================================================+
| Nom de l'hôte                                                     | Indique le nom de l'hôte                                               |
+-------------------------------------------------------------------+------------------------------------------------------------------------+
| Service (si on utilise la page Services)                          | Affiche le service concerné par le commentaire                         |
+-------------------------------------------------------------------+------------------------------------------------------------------------+
| Date de saisie                                                    | Affiche la date et l'heure où le commentaire a été saisi               |
+-------------------------------------------------------------------+------------------------------------------------------------------------+
| Auteur                                                            | Affiche la personne ayant ajouté ce commentaire                        |
+-------------------------------------------------------------------+------------------------------------------------------------------------+
| Commentaires                                                      | Affiche le contenu du commentaire                                      |
+-------------------------------------------------------------------+------------------------------------------------------------------------+
| Acquittement persistant en cas de redémarrage de l'ordonnanceur   | Indique si le commentaire reste après le redémarrage de l'ordonnanceur |
+-------------------------------------------------------------------+------------------------------------------------------------------------+

Filtres disponibles
-------------------

Vous pouvez filtrer le résultat présenté via les filtres suivants :

* **Nom de l'hôte** : permet de filtrer par nom d'hôte via une recherche de type SQL LIKE.
* **Service** : permet de filtrer par le nom du service.
* **Statut détaillé** : permet de filtrer par le statut détaillé des services.

.. note::
    La recherche sur les champs texte ne commence qu'à partir de la saisie du troisième caractère.

.. |pause|    image:: /images/pause.png
.. |refresh|    image:: /images/refresh.png
.. |resume|    image:: /images/resume.png
.. |graphperformances|    image:: /images/graphperformances.png
.. |url_link|    image:: /images/url_link.png
.. |passive_service|    image:: /images/passive_service.png
.. |showservices|    image:: /images/showservices.png
.. |nonotifications|    image:: /images/nonotifications.png
.. |configure|    image:: /images/configure.png
.. |showservicesstatuts|    image:: /images/showservicesstatuts.png
.. |showlogs|    image:: /images/showlogs.png
.. |showgraphperf|    image:: /images/showgraphperf.png
.. |showresult|    image:: /images/showresult.png
