=========
Les hôtes
=========

Un hôte est une entité (la plupart du temps identifié par son adresse IP) qui correspond à une ressource du système d'informations.
Exemples : Un serveur, une imprimante réseau, un serveur NAS...

Tous les ajouts d'hôtes se font dans **Configuration** ==> **Hôtes** ==> **Ajouter**.

[METTRE UNE CAPTURE D'ECRAN]

***********************
Configuration de l'hôte
***********************

Informations générales
----------------------

 *	Le champ **Nom de l'hôte** définit le nom d'hôte qui sera utilisé par le moteur de supervision
 *	Le champ **Alias** indique l'alias de l'hôte
 *	Le champ **Adresse IP/DNS** : Adresse IP ou nom DNS de l'hôte. Le bouton **Resolve** permet de résoudre le nom de domaine en interrogeant le serveur DNS du serveur central.
 *	Les champs **Communauté SNMP & Version** contiennent respectivement le nom de la communauté ainsi que la version de SNMP
 *	Le champ **Surveillé depuis le collecteur** indique quel est le serveur de supervision chargé de superviser cet hôte
 *	Le champ **Modèles d'hôte** permet d'associer un ou plusieurs modèles d'hôtes à l'hôte. Pour ajouter un modèle d'hôte, cliquez sur [METTRE UNE IMAGE]
 
 En cas de conflits de paramètres, le modèle d'hôte au dessus écrase les modèles d'hôtes en dessous.
 Le bouton [METTRE UNE IMAGE] permet de déplacer le modèle d'hôte. Le bouton [METTRE UNE IMAGE] permet de supprimer le modèle d'hôte.
 
 *	Si le champ **Créer aussi les services liés au modèle** est définit à **Oui**, Centreon génère automatiquement les services en se basant sur les modèles de services liés aux modèles d'hôtes définis au dessus.

Propriétés du contrôle de l'hôte
--------------------------------

 *	Le champ **Période de contrôle** définit la période temporelle durant laquelle l'ordonnanceur vérifie le statut
 *	Le champ **Commande de vérification** indique la commande utilisée pour vérifier la disponibilité de l'hôte
 *	Le champ **Arguments** définit les arguments donnés à la commande de vérification (chaque argument commence avec un "!")
 *	Le champ **Nombre de contrôles avant validation de l'état** définit le nombre de contrôle à effectuer avant de valider le statut de l'hôte : lorsque le statut est validé, une notification est envoyée
 *	Le champ **Intervalle normal de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut de l'hôte est OK
 *	Le champ **Intervalle non-régulier de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut de l'hôte est non-OK
 *	Les champs **Contrôle actif activé** et **Contrôle passif activé** active/Désactive les contrôles actifs et passifs

Macros
------

La partie **Macros** permet d'ajouter des macros personnalisés.
Les champs **Nom de la macro** et **Valeur de la macro** permettent respectivement de définit le nom et la valeur de la macro.
La case **Mot de passe** permet de cacher la valeur de la macro.

Pour supprimer la macro, cliquez sur [METTRE UNE IMAGE].
Pour déplacer l'ordre des macros, cliquez sur [METTRE UNE IMAGE].

Notification
------------

 *	Le champ **Notification activée** permet d'activer ou de désactiver les notifications pour l'objet
 *	Si la case **Contact additive inheritance** [PAS DE TRADUCTION DISPONIBLE] est cochée, alors Centreon n'écrase pas la configuration du modèle d'hôte parent mais ajoute les contacts en complément des contacts définis au niveau du modèle parent
 *	La liste **Contacts liés** indique les contacts qui recevront les notifications
 *	Si la case **Contact group additive inheritance** [PAS DE TRADUCTION DISPONIBLE] : est cochée, alors Centreon n'écrase pas la configuration du modèle d'hôte parent mais ajoute les groupes de contacts en complément des groupes de contacts définis au niveau du modèle parent
 *	Au sein de la liste **Groupe de contacts liés** tous les contacts appartenant aux groupes de contacts définis recevront les notifications
 *	Le champ **Intervalle de notification** est exprimé en minutes. Il indique la durée entre chaque envoi de notification (si la valeur est définie à 0 alors l'ordonnanceur envoie une seule notification)
 *	Le champ **Période de notification** indique la période temporelle durant laquelle les notifications seront activés
 *	Les **Options de notifications** définissent les statuts pour lesquels une notification sera envoyé
 *	Le **Délai de première notification** est exprimé en minutes. Il fait référence au délai à respecter avant l'envoi d'une première notification

****************
Onglet Relations
****************

 *	La liste **Groupes d'hôtes parents** définit les groupes d'hôtes auquel l'hôte appartient
 *	La liste **Catégorie d'hôtes parents** définit les catégories auquel l'hôte appartient
 *	La liste **Hôtes parents** définit les hôtes dont dépendant l'hôte
 *	La liste **Hôtes enfants** définit les hôtes qui dépendent de cet hôte

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
 *	Le champ **Gestionnaire d'évènements** définit la commande à exécuter si le gestionnaire d'évènement est activé
 *	Le champ **Arguments** définit les arguments à passer à la commande à exécuter

*********************************
Informations détaillées de l'hôte
*********************************

Moteur de supervision
---------------------

 *	Le champ **URL** définit une URL qui peut être utilisée pour donner davantage d'informations sur l'hôte
 *	Le champ **Notes** permet d'ajouter des notes optionnelles concernant l'hôte
 *	Le champ **URL d'action** définit une URL habituellement utilisée pour donner des informations d'actions sur l'hôte (maintenance...)
 *	Le champ **Icône** indique l'icône à utiliser pour l'hôte
 *	Le champ **Icône alternative** est le texte utilisé si l'icône ne peut être affichée
 *	Le champ **Niveau de criticité** indique le niveau de criticité de l'hôte

Les champs présentés ci-dessous sont des champs utilisés uniquement par la CGI de l'ordonnanceur (habituellement Nagios). Par conséquent, ils présentent peu d'intérêt lorsqu'on utilise Centreon Engine et Centreon Broker.

 *	Le champ **Image VRML** définit le logo pour le moteur 3D de l'hôte
 *	Le champ **Image de la carte des états** définit le logo pour la CGI de l'ordonnanceur
 *	Le champ **Coordonnées 2D et 3D** indiquent les coordonées 2D et 3D utilisé par la CGI
 
Informations supplémentaires
---------------------------- 
 
 *	Le champ **Statut** permet d'activer ou de désactiver l'hôte
 *	Le champ **Commentaires** permet d'ajouter un commentaire concernant l'hôte