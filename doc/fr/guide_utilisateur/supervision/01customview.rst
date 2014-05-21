=======================
Les vues personnalisées
=======================

************
Présentation
************

Les vues personnalisées permettent à chaque utilisateur d'avoir sa propre vue de la supervision.
Une vue peut contenir de 1 à 3 colonnes. Chaque colonne peut contenir des widgets.

Un widget est un module permettant de visualiser certaines informations sur certains objets.
Il est possible d'insérer au sein d'une même vue plusieurs widgets de différents types.
Par défaut, MERETHIS propose des widgets permettant d'obtenir des informations sur : les hôtes, les groupes d'hôtes,
les services, les groupes de services. Enfin, un dernier widget permet de visualiser les graphiques de performances en temps réel.

****************
Gestion des vues
****************

Toutes les manipulations ci-dessous se déroulent au sein de la page **Accueil** ==> **Vues personnalisées**. Cette page est également la première page affichée lors de la connexion
d'un utilisateur au sein de Centreon.

Ajouter une vue
===============

Pour ajouter une vue, cliquez sur **Ajouter une vue**.

.. image :: /images/guide_utilisateur/supervision/01addview.png
   :align: center 

* Le champ **Nom de la vue** indique le nom de la vue qui sera visible par l'utilisateur
* Le champ **Mise en page** permet de choisir le nombre de colonne de la vue

Pour modifier une vue existante, cliquez sur **Editer une vue**.

.. note::
    La diminution du nombre de colonnes enlève les widgets associées à la colonne.

Partager une vue
================

Il est possible de partager une vue existante avec un ou plusieurs utilisateurs.
Pour cela, cliquez sur **Partager la vue**.

* Si le champ **Verrouillée** est définit à **Oui**, alors les autres utilisateurs ne pourront pas modifier la vue
* Le champ **Liste des utilisateurs** permet de définir les utilisateurs avec lesquels est partagée la vue
* Le champ **Liste des groupes utilisateur** permet de définir les groupes d'utilisateurs avec lesquels est partagée la vue

.. _leswidgets:

Insérer un widget
=================

Pour ajouter un widget, cliquez sur **Ajouter un widget**.

.. image :: /images/guide_utilisateur/supervision/01addwidget.png
   :align: center 

* Le champ **Titre du widget** permet de définir un nom pour son widget
* Choisissez dans le tableau en dessous le type de widget que vous souhaitez ajouter

Personnaliser son widget
========================

Il est possible de déplacer un widget en faisant un drag-and-drop depuis la barre de titre.
Pour réduire un widget, cliquez sur |reducewidget|.
Par défaut, les informations contenues au sein du widget sont rafraîchis de manière régulière.
Pour les rafraîchir manuellement, cliquez sur |refreshwidget|.

Pour personnaliser son widget, cliquez sur |configurewidget|.

Supprimer un widget
===================

Il est possible de supprimer le widget en cliquant sur |deletewidget|.

******************
Détail des widgets
******************

Les paragraphes ci-dessous détaillent les attributs de chaque widget après avoir cliqué sur |configurewidget|.

Le widget d'hôtes
=================

Filters
-------

* Le champ **Host Name Search** permet de faire une recherche sur un ou plusieurs noms d'hôtes
* Si la case **Display Up** est cochée, les hôtes en statut UP seront affichés
* Si la case **Display Down** est cochée, les hôtes en statut DOWN seront affichés
* Si la case **Display Unreachable** est cochée, les hôtes en statut UNREACHABLE seront affichés
* La liste **Acknowledgement Filter** permet d'afficher les hôtes acquittés ou non acquittés (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **Downtime Filter** permet d'afficher les hôtes qui subissent un temps d'arrêt ou non (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **State Type** permet d'afficher les hôtes en état SOFT ou HARD (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **Hostgroup** permet d'afficher les hôtes appartenant à un certain groupe d'hôtes (si la liste est vide, tous les hôtes seront affichés)
* La liste **Results** limite le nombre de résultats

Columns
-------

* Si la case **Display Host Name** est cochée, alors le nom d'hôte sera affiché
* Si la case **Display Output** est cochée, alors le message associé au statut de l'hôte sera affiché
* La liste **Output Length** permet de limiter la longueur du message affiché
* Si la case **Display Status** est cochée, alors le statut de l'hôte est affiché
* Si la case **Display IP** est cochée, alors l'adresse IP de l'hôte est affichée
* Si la case **Display Last Check** est cochée, alors la date et l'horaire de la dernière vérification sont affichés
* Si la case **Display Duration** est cochée, alors la durée durant laquelle l'hôte a conservé son statut est affichée
* Si la case **Display Hard State Duration** est cochée, alors la durée durant laquelle l'hôte a conservé son état HARD est affichée
* Si la case **Display Tries** est cochée, alors le nombre d'essais avant la validation de l'état est affiché
* La liste **Order By** permet de classer les hôtes par ordre alphabétique suivant plusieurs paramètres

Misc
----

* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraîchissement des données

Le widget de services
=====================

Filters
-------

* Le champ **Host Name** permet de faire une recherche sur un ou plusieurs noms d'hôtes
* Le champ **Service Description** permet de faire une recherche sur un ou plusieurs noms de services
* Si la case **Display Ok** est cochée, les services en statut OK seront affichés
* Si la case **Display Warning** est cochée, les services en statut WARNING seront affichés
* Si la case **Display Critical** est cochée, les services en statut CRITICAL seront affichés
* Si la case **Display Unknown** est cochée, les services en statut UNKNOWN seront affichés
* Si la case **Display Pending** est cochée, les services en statut PENDING seront affichés
* La liste **Acknowledgement Filter** permet d'afficher les services acquittés ou non acquittés (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **Downtime Filter** permet d'afficher les services qui subissent un temps d'arrêt ou non (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **State Type** permet d'afficher les services en état SOFT ou HARD (si la liste est vide, les deux types d'hôtes seront affichés)
* La liste **Hostgroup** permet d'afficher les services appartenant à des hôtes faisant partie d'un certain groupe d'hôtes (si la liste est vide, tous les services seront affichés)
* La liste **Servicegroup** permet d'afficher les services appartenant à un certain groupe de services (si la liste est vide, tous les services seront affichés)
* La liste **Results** limite le nombre de résultats

Columns
-------

* Si la case **Display Host Name** est cochée, alors le nom d'hôte sera affiché
* Si la case **Display Service Description** est cochée, alors le nom du service sera affiché
* Si la case **Display Output** est cochée, alors le message associé au statut du service sera affiché
* La liste **Output Length** permet de limiter la longueur du message affiché
* Si la case **Display Status** est cochée, alors le statut du service est affiché
* Si la case **Display Last Check** est cochée, alors la date et l'horaire de la dernière vérification sont affichés
* Si la case **Display Duration** est cochée, alors la durée durant laquelle le service a conservé son statut est affichée
* Si la case **Display Hard State Duration** est cochée, alors la durée durant laquelle le service a conservé son état HARD est affichée
* Si la case **Display Tries** est cochée, alors le nombre d'essais avant la validation de l'état est affiché
* La liste **Order By** permet de classer les services par ordre alphabétique suivant plusieurs paramètres

Misc
----

* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de graphique de performance
=====================================

* Le champ **Service** permet de choisir le service pour lequel le graphe sera affiché
* La liste **Graph period** permet de choisir la période de temps que le graphe doit afficher
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de groupe d'hôtes
===========================

* Le champ **Hostgroup Name Search** permet de choisir les groupes d'hôtes affichés
* Si la case **Enable Detailed Mode** est cochée, alors tous les noms d'hôtes ainsi que les services associés à ces hôtes seront affichés pour les groupes d'hôtes sélectionnés
* La liste **Results** permet de limiter le nombre de résultats
* La liste **Order By** permet de classer les groupes d'hôtes par ordre alphabétique suivant plusieurs paramètres
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

Le widget de groupes de services
================================

* Le champ **Servicegroup Name Search** permet de choisir les groupes de services affichés
* Si la case **Enable Detailed Mode** est cochée, alors tous les noms d'hôtes ainsi que les services associés à ces hôtes seront affichés pour les groupes de services sélectionnés
* La liste **Results** permet de limiter le nombre de résultats
* La liste **Order By** permet de classer les groupes de services par ordre alphabétique suivant plusieurs paramètres
* Le champ **Refresh Interval (seconds)** permet de définir la durée avant le rafraichissement des données

.. |deletewidget|    image:: /images/guide_utilisateur/supervision/deletewidget.png
.. |configurewidget|    image:: /images/guide_utilisateur/supervision/configurewidget.png
.. |refreshwidget|    image:: /images/guide_utilisateur/supervision/refreshwidget.png
.. |reducewidget|    image:: /images/guide_utilisateur/supervision/reducewidget.png
