============
Les services
============

Un service est un point de contrôle sur un hôte.
Par exemple : Taille d'une partition sur un serveur, niveau d'encre sur une imprimante

Tous les ajouts de services se font dans **Configuration** ==> **Services** ==> **Ajouter**.

[ TODO METTRE UNE CAPTURE D'ECRAN]

************************
Configuration du service
************************

General Information
-------------------

*	Le champ **Description** définit le nom du service
*	Le champ **Modèle de service** indique le modèle de service auquel le service est lié

Etat du service
---------------

*	Le champ **Est volatile** indique si le service est volatile ou non (d'une manière générale uniquement les services passifs sont volatiles)
*	Le champ **Période de contrôle** définit la période temporelle durant laquelle l'ordonnanceur vérifie le statut du service
*	Le champ **Commande de vérification** indique la commande utilisée pour vérifier la disponibilité du service
*	Le tableau **Arguments** définit les arguments donnés à la commande de vérification (le nombre d'arguments varie en fonction de la commande de vérification choisie)
*	Le champ **Nombre de contrôles avant validation de l'état** définit le nombre de contrôles à effectuer avant de valider le statut du service : lorsque le statut est validé, une notification est envoyée
*	Le champ **Intervalle normal de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut du service est OK
*	Le champ **Intervalle non-régulier de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut du service est non-OK
*	Les champs **Contrôle actif activé** et **Contrôle passif activé** activent/désactivent les contrôles actifs et passifs

Macros
------

La partie **Macros** permet d'ajouter des macros personnalisés.
Les champs **Nom de la macro** et **Valeur de la macro** permettent respectivement de définit le nom et la valeur de la macro.
La case **Mot de passe** permet de cacher la valeur de la macro.

Pour supprimer la macro, cliquez sur [ TODO METTRE UNE IMAGE].
Pour déplacer l'ordre des macros, cliquez sur [ TODO METTRE UNE IMAGE].

Notification
------------

*	Le champ **Notification activée** permet d'activer ou de désactiver les notifications pour l'objet
*	Le champ **Inherit contacts from host** [ TODO PAS DE TRADUCTION DISPONIBLE : j'ai proposé une traduction sur la plateforme] permet de faire hériter les contacts depuis la configuration de l'hôte
*	Si la case **Contact additive inheritance** [ TODO PAS DE TRADUCTION DISPONIBLE] est cochée, alors Centreon n'écrase pas la configuration du modèle de service parent mais ajoute les contacts en complément des contacts définis au niveau du modèle parent
*	La liste **Contacts liés** indique les contacts qui recevront les notifications
*	Si la case **Contact group additive inheritance** [ TODO PAS DE TRADUCTION DISPONIBLE] est cochée, alors Centreon n'écrase pas la configuration du modèle de service parent mais ajoute les groupes de contacts en complément des groupes de contacts définis au niveau du modèle parent
*	Au sein de la liste **Groupe de contacts liés** tous les contacts appartenant aux groupes de contacts définis recevront les notifications
*	Le champ **Intervalle de notification** est exprimé en minutes. Il indique la durée entre chaque envoi de notification (si la valeur est définie à 0 alors l'ordonnanceur envoie une seule notification)
*	Le champ **Période de notification** indique la période temporelle durant laquelle les notifications seront activées
*	Les **Options de notifications** définissent les statuts pour lesquels une notification sera envoyée
*	Le **Délai de première notification** est exprimé en minutes. Il fait référence au délai à respecter avant l'envoi d'une première notification

****************
Onglet Relations
****************

Relations
---------

*	La liste **Lié aux hôtes** permet de définir le ou les hôtes liés à ce service
*	La liste **Lié aux groupes de services** permet de lier le service à un ou plusieurs groupes de services

Traps SNMP
----------

Le champ **Traps SNMP reliés au service** permet de définir les traps SNMP qui seront affichés par le service.

**********************
Traitement des données
**********************

*	Si le **Contrôle de vérification de l'hôte** est activé, alors la commande de remontée des contrôles de l'hôte sera activée
*	Le champ **Contrôler la fraicheur du résultat** permet d'activer ou de désactiver le contrôle de fraicheur du résultat
*	Le champ **Seuil de fraicheur du résultat** est exprimé en secondes. Si durant cette durée il n'y a pas de changement d'état de l'hôte alors la commande de vérification est executée
*	Le champ **Détection de bagotage des status** permet d'activer ou de désactiver la détection du bagotage des statuts
*	Les champs **Seuil bas de détection de bagotage des statuts** et **Seuil haut de détection de bagotage des statuts** définissent les seuils hauts et bas pour la détection du bagotage
*	Le champ **Traitement des données de performances** permet d'activer ou de désactiver le traitement des données de performances (et donc la génération des graphiques de performances)
*	Les champs **Rétention des informations de statut** et **Rétention et des informations concernant pas le statut** indiquent si les informations concernant ou ne concernant pas le statut sont sauvegardées après chaque relance de la commande de vérification
*	Le champ **Options à enregistrer** définit les options à enregistrer si la rétention est activée
*	Le champ **Gestionnaire d'évènements activé** permet d'activer ou de désactiver le gestionnaire d'évènements
*	Le champ **Gestionnaire d'évènements** définit la commande à exécuter si le gestionnaire d'évènements est activé
*	Le champ **Arguments** définit les arguments à passer à la commande à exécuter

***************************************
Informations supplémentaires du service
***************************************

Centreon
--------

*	**Modèle de graphique** : Définit le modèle de graphique à utiliser pour le service
*	**Catégories** : Définit la catégorie auquel le service appartient

Moteur de supervision
---------------------

*	Le champ **URL** définit une URL qui peut être utilisée pour donner davantage d'informations sur le service
*	Le champ **Notes** permet d'ajouter des notes optionnelles concernant le service
*	Le champ **URL d'action** définit une URL habituellement utilisée pour donner des informations d'actions sur le service (maintenance...)
*	Le champ **Icône** indique l'icône à utiliser pour le service
*	Le champ **Icône alternative** est le texte utilisé si l'icône ne peut être affichée
*	Le champ **Niveau de criticité** indique le niveau de criticité du service

Informations supplémentaires
---------------------------- 

*	Le champ **Statut** permet d'activer ou de désactiver le service
*	Le champ **Commentaires** permet d'ajouter un commentaire concernant le service

************************
Détachement d'un service
************************

Si un service est lié à plusieurs hôtes, la granularité des données n'est pas possible : si l'on souhaite modifier le service uniquement pour un seul hôte l'opération n'est pas possible.
C'est pourquoi il est possible de transformer ce service lié à plusieurs hôtes en un service unique pour chaque hôte :

#.	Dans la liste des services, sélectionnez le service liés à plusieurs hôtes (habituellement ce service est surligné en orange)
#.	Dans le menu **More actions...** cliquez sur **Détacher** puis validez

Il existe maintenant un service unique par hôte.
