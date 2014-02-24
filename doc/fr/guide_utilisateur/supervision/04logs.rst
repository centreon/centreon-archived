=========================
Les journaux d'évènements
=========================

**********
Définition
**********

Les journaux d'évènements (aussi appelés "Event logs") permettent de :

* Visualiser les différents changements de statuts et états des objets supervisés
* Voir les notifications envoyées ainsi que leurs destinataires

Ces journaux peuvent être visualisées sur une période donnée.

*************
Visualisation
*************

Pour visualiser les journaux d'évènements, rendez-vous dans le menu **Supervision** ==> **Journaux d'évènements**.

[ TODO Mettre l'image : images/02.png]

Le menu de gauche permet de sélectionner les hôtes et/ou les services pour lesquels on souhaite visualiser les journaux d'évènements.

La barre de recherche grise appelée **Période de visualisation** permet de sélectionner la période de temps pour laquelle on souhaite visualiser les évènements.
La liste déroulante permet de sélectionner des périodes de temps génériques. Si la liste déroulante est vide alors il est possible de choisir manuellement la période de temps en utilisant les champs **Du** et **Au**.

La barre de recherche grisée située en dessous permet de sélectionner filtres de recerche afin d'afficher les évènements souhaités.

Le tableau permet de visualiser les résultats.

********************
Filtrer les messages
********************

Type de message
===============

Il est possible d'afficher plusieurs types de messages sur la période donnée:

* Les services ayant le statut WARNING en cochant **Alerte**
* Les erreurs (hôtes non disponibles ou services ayant le statut CRITICAL) en cochant **Erreur**
* Les incidants d'hôtes ou de service validés ('HARD') en cliquant sur **Etat Hard seulement**
* Les notifications envoyées en cliquant sur **Notifications**

Statut de l'hôte ou du service
==============================

Notez bien que les choix effectuées ici influencent les cases cochées dans **Type de message**.
Il est également possible de visualiser les différents messages en sélectionnant manuellement les statuts désirés pour les hôtes ou les services.

*************
Les résultats
*************

Le tableau ci-dessous décrit les différentes colonnes du tableau de résultats.

+---------------------+-----------------------------------------------------------------------------------------------------------+
|   Nom de la colonne |   Description                                                                                             | 
+=====================+===========================================================================================================+
| Jour                | Affiche la date de l'évènement                                                                            |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Heure               | Affiche l'heure de l'évènement                                                                            |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Nom de l'objet      | Affiche le nom de l'objet (hôte et/ou service)                                                            |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Statut              | Affiche le statut de l'objet                                                                              |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Type                | Affiche l'état de l'objet ('SOFT' ou 'HARD')                                                              |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Nombre d'essais     | Affiche le nombre de vérification depuis le statut actuel de l'objet                                      |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Statut détaillé     | Affiche le message expliquant le statut de l'hôte ou du service                                           |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Contact             | Affiche le contact ayant été contacté (n'est renseigné que s'il s'agit d'une notification)                |
+---------------------+-----------------------------------------------------------------------------------------------------------+
| Commande            | Affiche la commande utilisée pour alerter le contact (n'est renseigné que s'il s'agit d'une notification) |
+---------------------+-----------------------------------------------------------------------------------------------------------+

