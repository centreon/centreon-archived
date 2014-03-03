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
*	Le champ **Adresse IP/DNS** : Adresse IP ou nom DNS de l'hôte. Le bouton **Resolve** permet de résoudre le nom de domaine en interrogeant le serveur DNS configuré sur le serveur central.
*	Les champs **Communauté SNMP & Version** contiennent respectivement le nom de la communauté ainsi que la version de SNMP.
*	Le champ **Surveillé depuis le collecteur** indique quel est le serveur de supervision chargé de superviser cet hôte.
*	Le champ **Modèles d'hôte** permet d'associer un ou plusieurs modèles d'hôtes à l'hôte. Pour ajouter un modèle d'hôte, cliquer sur le symbole "+" [TODO mettre icone].
 
 En cas de conflits de paramètres présent sur plusieurs modèles, le modèle d'hôte au dessus écrase les modèles d'hôtes en dessous.
 Le bouton |movelist| permet de déplacer le modèle d'hôte. Le bouton |deletelist| permet de supprimer le modèle d'hôte.
 
*	Si le champ **Créer aussi les services liés au modèle** est définit à **Oui**, Centreon génère automatiquement les services en se basant sur les modèles de services liés aux modèles d'hôtes définis au dessus.

Propriétés du contrôle de l'hôte
================================

*	Le champ **Période de contrôle** définit la période temporelle durant laquelle l'ordonnanceur vérifie le statut.
*	Le champ **Commande de vérification** indique la commande utilisée pour vérifier la disponibilité de l'hôte.
*	Le champ **Arguments** définit les arguments donnés à la commande de vérification (chaque argument commence avec un "!").
*	Le champ **Nombre de contrôles avant validation de l'état** définit le nombre de contrôle à effectuer avant de valider le statut de l'hôte : lorsque le statut est validé, une notification est envoyée.
*	Le champ **Intervalle normal de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut de l'hôte est OK.
*	Le champ **Intervalle non-régulier de contrôle** est exprimé en minutes. Il définit l'intervalle de validation du statut non-OK de l'hôte.
*	Les champs **Contrôles actifs activés** et **Contrôles passifs activés** activent/désactivent les contrôles actifs et passifs.

Macros
======

La partie **Macros** permet d'ajouter des macros personnalisées.

* Les champs **Nom de la macro** et **Valeur de la macro** permettent respectivement de définir le nom et la valeur de la macro.
* La case **Mot de passe** permet de cacher la valeur de la macro.

Pour supprimer la macro, cliquez sur |deletelist|.
Pour déplacer l'ordre des macros, cliquez sur |movelist|.

Notification
============

*	Le champ **Notification activée** permet d'activer ou de désactiver les notifications pour l'objet.
*	Si la case **Contact additive inheritance** [ TODO PAS DE TRADUCTION DISPONIBLE] est cochée, alors Centreon n'écrase pas la configuration du modèle d'hôte parent mais ajoute les contacts en complément des contacts définis au niveau du modèle parent.
*	La liste **Contacts liés** indique les contacts qui recevront les notifications.
*	Si la case **Contact group additive inheritance** [ TODO PAS DE TRADUCTION DISPONIBLE] : est cochée, alors Centreon n'écrase pas la configuration du modèle d'hôte parent mais ajoute les groupes de contacts en complément des groupes de contacts définis au niveau du modèle parent.
*	Au sein de la liste **Groupe de contacts liés** tous les contacts appartenant aux groupes de contacts définis recevront les notifications.
*	Le champ **Intervalle de notification** est exprimé en minutes. Il indique la durée entre chaque envoi de notification lorsque le statut est non-OK. Si la valeur est définie à 0 alors l'ordonnanceur envoie une seule notification par changement de statut.
*	Le champ **Période de notification** indique la période temporelle durant laquelle les notifications seront activées.
*	Les **Options de notifications** définissent les statuts pour lesquels une notification sera envoyée.
*	Le **Délai de première notification** est exprimé en minutes. Il fait référence au délai à respecter avant l'envoi d'une première notification lorsqu'un statut non-OK est validé.

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
*	Le champ **Contrôler la fraicheur du résultat** permet d'activer ou de désactiver le contrôle de fraicheur du résultat.
*	Le champ **Seuil de fraicheur du résultat** est exprimé en secondes. Si durant cette période aucune demande de changement de statut de l'hôte (commande passive) n'a été reçue alors la commande de vérification est exécutée.
*	Le champ **Détection de bagotage des status** permet d'activer ou de désactiver la détection du bagotage des statuts (statut changant trop fréquement de valeur sur une période données).
*	Les champs **Seuil bas de détection de bagotage des statuts** et **Seuil haut de détection de bagotage des statuts** définissent les seuils hauts et bas pour la détection du bagotage en pourcentage de changement.
*	Le champ **Traitement des données de performances** permet d'activer ou de désactiver le traitement des données de performances (et donc la génération des graphiques de performances). Cette option est inutile dans Centreon.
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

*	Le champ **Image VRML** définit le logo pour le moteur 3D de l'hôte.
*	Le champ **Image de la carte des états** définit le logo pour la CGI de l'ordonnanceur.
*	Le champ **Coordonnées 2D et 3D** indiquent les coordonnées 2D et 3D utilisées par la CGI.

Access groups
=============

*   Le champ **ACL Resource Groups**, visible que pour les utilisateurs non administreur, permet de lier l'hôte à un groupe afin de visualiser ce dernier. [TODO ajouter ref sur chapitre ACL].

Informations supplémentaires
============================
 
*	Le champ **Statut** permet d'activer ou de désactiver l'hôte.
*	Le champ **Commentaires** permet d'ajouter un commentaire concernant l'hôte.

.. |deletelist|    image:: /images/deletelist.png
.. |movelist|    image:: /images/movelist.png
