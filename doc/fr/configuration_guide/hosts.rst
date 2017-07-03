.. _hostconfiguration:

=========
Les hôtes
=========

Un hôte est toute entité possédant une adresse IP correspondant à une ressource du système d'informations.
Exemples : Un serveur, une imprimante réseau, un serveur NAS, une base de données, une sonde de température, une caméra IP...

Tous les ajouts d'hôtes se font dans le menu **Configuration** ==> **Hôtes** ==> **Ajouter**.

.. image :: /images/guide_utilisateur/configuration/02addhost.png
   :align: center 

***********************
Configuration de l'hôte
***********************

Informations générales
======================

*	Le champ **Nom de l'hôte** définit le nom d'hôte qui sera utilisé par le moteur de supervision.
*	Le champ **Alias** indique l'alias de l'hôte.
*	Le champ **Adresse IP/DNS** : Adresse IP ou nom DNS de l'hôte. Le bouton **Résoudre** permet de résoudre le nom de domaine en interrogeant le serveur DNS configuré sur le serveur central.
*	Les champs **Communauté SNMP & Version** contiennent respectivement le nom de la communauté ainsi que la version SNMP.
*	Le champ **Surveillé depuis le collecteur** indique quel est le serveur de supervision chargé de superviser cet hôte.
*	Le champ **Modèles d'hôte** permet d'associer un ou plusieurs modèles d'hôtes à cet objet.
 
 En cas de conflits de paramètres présents sur plusieurs modèles, le modèle d'hôte au-dessus écrase les propriétés identiques définies dans modèles d'hôtes en dessous.
 Le bouton |move| permet de déplacer l'ordre des modèles d'hôte. Le bouton |delete| permet de supprimer le modèle d'hôte.
 
*	Si le champ **Créer aussi les services liés au modèle** est définit à **Oui**, Centreon génère automatiquement les services en se basant sur les modèles de services liés aux modèles d'hôtes définis au-dessus (voir chapitre :ref:`hosttemplates`). 

Propriétés du supervison de l'hôte
==================================

*       Le champ **Commande de vérification** indique la commande utilisée pour vérifier la disponibilité de l'hôte.
*       Le champ **Arguments** définit les arguments donnés à la commande de vérification (chaque argument commence avec un "!").

La partie **Macros** permet d'ajouter des macros personnalisées.

*       Les champs **Nom de la macro** et **Valeur de la macro** permettent respectivement de définir le nom et la valeur de la macro.
*       La case **Mot de passe** permet de cacher la valeur de la macro.

Pour réinitialiser la macro avec sa valeur par défaut (définie dans le templae) cliquez sur |undo|.

Pour afficher la description de la macro, cliquez sur |description|.

Pour supprimer la macro, cliquez sur |delete|.

Pour déplacer l'ordre des macros, cliquez sur |move|.


Propriété d'ordonancement de l'hôte
===================================

*	Le champ **Période de contrôle** définit la période temporelle durant laquelle l'ordonnanceur vérifie le statut de l'objet.
*	Le champ **Nombre de contrôles avant validation de l'état** définit le nombre de contrôle à effectuer avant de valider le statut de l'hôte : lorsque le statut est validé, le processus de notification est enclenché.
*	Le champ **Intervalle normal de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut de l'hôte est OK.
*	Le champ **Intervalle non-régulier de contrôle** est exprimé en minutes. Il définit l'intervalle de validation du statut non-OK de l'hôte.
*	Les champs **Contrôles actifs activés** et **Contrôles passifs activés** activent/désactivent les contrôles actifs et passifs.


Onglet Notification
===================

*       Le champ **Notification activée** permet d'activer ou de désactiver les notifications concernant l'objet.
*       Les **Options de notifications** définissent les statuts pour lesquels une notification sera envoyée.
*       Le champ **Intervalle de notification** est exprimé en minutes. Il indique la durée entre chaque envoi de notification lorsque le statut est non-OK. Si la valeur est définie à 0 alors l'ordonnanceur envoie une seule notification par changement de statut.
*       Le champ **Période de notification** indique la période temporelle durant laquelle les notifications seront activées.
*       Le **Délai de première notification** est exprimé en minutes. Il fait référence au délai à respecter avant l'envoi d'une première notification lorsqu'un statut non-OK est validé.
*       La liste **Contacts liés** indique les contacts qui recevront les notifications.
*       Au sein de la liste **Groupe de contacts liés** tous les contacts appartenant aux groupes de contacts définis recevront les notifications.

****************
Onglet Relations
****************

*	La liste **Groupes d'hôtes parents** définit les groupes d'hôtes auxquels l'hôte appartient.
*	La liste **Catégorie d'hôtes parents** définit les catégories auxquelles l'hôte appartient.
*	La liste **Hôtes parents** permet de définir les relations physiques de parenté entre objet.
*	La liste **Hôtes enfants** permet de définir les relations physiques de parenté entre objet.

**********************
Traitement des données
**********************

*	Si le **Contrôle de vérification de l'hôte** est activé, alors la commande de remontée des contrôles de l'hôte sera activée.
*	Le champ **Contrôler la fraîcheur du résultat** permet d'activer ou de désactiver le contrôle de fraîcheur du résultat.
*	Le champ **Seuil de fraîcheur du résultat** est exprimé en secondes. Si durant cette période aucune demande de changement de statut de l'hôte (commande passive) n'a été reçue alors la commande de vérification active est exécutée.
*	Le champ **Détection de bagotage des status** permet d'activer ou de désactiver la détection du bagotage des statuts (statut changeant trop fréquemment de valeur sur une période donnée).
*	Les champs **Seuil bas de détection de bagotage des statuts** et **Seuil haut de détection de bagotage des statuts** définissent les seuils hauts et bas pour la détection du bagotage en pourcentage de changement de statuts.
*	Le champ **Traitement des données de performances** permet d'activer ou de désactiver le traitement des données de performances (et donc la génération des graphiques de performances). Cette option est inutile dans le cas où Centreon Broker est utilisé.
*	Les champs **Rétention des informations de statut** et **Rétention des informations ne concernant pas le statut** indiquent si les informations concernant ou non le statut sont sauvegardées après chaque relance de la commande de vérification.
*	Le champ **Options à enregistrer** définit les options à enregistrer si la rétention est activée.
*	Le champ **Gestionnaire d'évènements activé** permet d'activer ou de désactiver le gestionnaire d'évènements.
*	Le champ **Gestionnaire d'évènements** définit la commande à exécuter si le gestionnaire d'évènements est activé.
*	Le champ **Arguments** définit les arguments de la commande du gestionnaire d'évènements.

*********************************
Informations détaillées de l'hôte
*********************************

Moteur de supervision
=====================

*	Le champ **URL** définit une URL qui peut être utilisée pour donner davantage d'informations sur l'hôte.
*	Le champ **Notes** permet d'ajouter des notes optionnelles concernant l'hôte.
*	Le champ **URL d'action** définit une URL habituellement utilisée pour donner des informations d'actions sur l'hôte (maintenance...).
*	Le champ **Icône** indique l'icône à utiliser pour l'hôte.
*	Le champ **Icône alternative** est le texte utilisé si l'icône ne peut être affichée.
*	Le champ **Niveau de criticité** indique le niveau de criticité de l'hôte.

Les champs présentés ci-dessous sont des champs utilisés uniquement par la CGI de l'ordonnanceur (habituellement Nagios). Par conséquent, ils présentent peu d'intérêt lorsqu'on utilise Centreon Engine et Centreon Broker.

*	Le champ **Image de la carte des états** définit le logo pour la CGI de l'ordonnanceur.
*	Le champ **Coordonnées géographique** indique les coordonnées géographiques (Latitude,Longitude) de l'élément. Ces informations sont utiles dans le logiciel Centreon Map.
*	Le champ **Coordonnées 2D et 3D** indiquent les coordonnées 2D et 3D utilisées par la CGI.

Access groups
=============

*   Le champ **ACL Resource Groups** (seulement visible pour les utilisateurs non administrateur), permet de lier l'hôte à un groupe d'hôtes afin de pouvoir visualiser ce dernier (voir chapitre :ref:`acl`).

Informations supplémentaires
============================
 
*	Le champ **Statut** permet d'activer ou de désactiver l'hôte.
*	Le champ **Commentaires** permet d'ajouter un commentaire concernant l'hôte.

.. |delete|    image:: /images/delete.png
.. |move|    image:: /images/move.png
.. |navigate_plus|    image:: /images/navigate_plus.png
.. |undo|    image:: /images/undo.png
.. |description|    image:: /images/description.png

