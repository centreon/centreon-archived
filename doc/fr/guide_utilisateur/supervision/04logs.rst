=========================
Les journaux d'évènements
=========================

**********
Définition
**********

Les journaux d'évènement (aussi appelés logs) permettent de :

* Visualiser les différents statuts et états des objets supervisés
* Voir les notifications envoyées ainsi que leurs destinataires

Ces journaux peuvent être visualisées sur une période donnée.

*************
Visualisation
*************

Pour visualiser les journaux d'évènements, rendez-vous dans **Supervision** ==> **Journaux d'évènements**.

[ TODO Mettre l'image : images/02.png]

Le menu de gauche permet de sélectionner les hôtes et/ou les services pour lesquels on souhaite visualiser les journaux d'évènements.

La barre de recherche grise appelée **Période de visualisation** permet de sélectionner la période de temps pour laquelle on souhaite visualiser les logs.
La liste déroulante permet de sélectionner des périodes de temps génériques. Si la liste déroulante est vide alors il est possible de choisir manuellement la période de temps en utilisant les champs **Du** et **Au**.

La barre de recherche grisée située en dessous permet de sélectionner les messages que l'on souhaite afficher.

Le tableau permet de visualiser les résultats.

********************
Filtrer les messages
********************

Type de message
---------------

Il est possible d'afficher plusieurs types de messages sur la période donnée:

* Les services ayant le statut WARNING en cochant **Alerte**
* Les erreurs (hôtes non disponibles ou services ayant le statut CRITICAL) en cochant **Erreur**
* Les hôtes ou les services ayant eu l'état HARD en cliquant sur **Etat Hard seulement**
* Les notifications envoyées en cliquant sur **Notifications**

Statut de l'hôte ou du service
------------------------------

Notez bien que les choix effectuées ici influencent les cases cochées dans **Type de message**.
Il est également possible de visualiser les différents messages en sélectionnant manuellement les statuts désirés pour les hôtes ou les services.

*************
Les résultats
*************

Le tableau ci-dessous décrit les différentes colonnes du tableau de résultats.

+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
|Nom de la colonne                                                                           |   Description                                                                                             | 
+============================================================================================+===========================================================================================================+
| Jour                                                                                       | Affiche la date de l'évènement                                                                            |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Heure                                                                                      | Affiche l'heure de l'évènement                                                                            |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Statut de l'hôte [ TODO Il me semble qu'il s'agit d'un bug ou d'une erreur de traduction ] | Affiche le nom de l'hôte et si nécessaire le service associé                                              |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Statut                                                                                     | Affiche le statut de l'objet                                                                              |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Type [ TODO Le mot Etat ne serait-il pas plus juste ?]                                     | Affiche l'état de l'objet                                                                                 |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| R                                                                                          | Affiche le nombre de vérification depuis le statut actuel de l'objet                                      |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Statut détaillé                                                                            | Affiche le message expliquant le statut de l'hôte ou du service                                           |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Contact                                                                                    | Affiche le contact ayant été contacté (n'est renseigné que s'il s'agit d'une notification)                |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+
| Commande                                                                                   | Affiche la commande utilisée pour alerter le contact (n'est renseigné que s'il s'agit d'une notification) |
+--------------------------------------------------------------------------------------------+-----------------------------------------------------------------------------------------------------------+