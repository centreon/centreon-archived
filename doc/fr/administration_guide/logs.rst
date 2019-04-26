=================================================
Journalisation des modifications de configuration
=================================================

********
Principe
********

Par défaut, Centreon conserve dans un journal toutes les actions utilisateurs concernant la modification de la configuration de la supervision.
Pour accéder à ces informations, rendez-vous dans le menu **Administration > Journal d'évènements**.

.. image :: /images/guide_exploitation/fsearchlogs.png
   :align: center

La barre de recherche grise vous permet de filtrer les informations présentées via les filtres :

* **Objet** permettant de filtrer sur le nom de l'objet (hôte, service, contact, définition de trap SNMP, groupe, ...)
* **Utilisateur** permettant de filtrer par auteur de modification
* **Type d'objet** permettant de filtrer par type d'objet modifié.

********
Pratique
********

Exemple : Pour voir toutes les actions faites par l'utilisateur **admin**, renseignez "admin" dans le champ **Utilisateur** puis cliquez sur **Rechercher**.

Le tableau ci-dessous définit les colonnes du tableau de résultats :

+----------------------+------------------------------------------------------------------------------------------------------------+
|   Colonne            |  Description                                                                                               | 
+======================+============================================================================================================+
| Heures               | Indique la date et l'heure de l'évènement                                                                  |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Type de modification | Contient le type d'action effectuée. Il existe plusieurs types d'actions possibles :                       |
|                      |                                                                                                            |
|                      | - Ajouté : Indique que l'objet a été ajouté                                                                |
|                      | - Modifié : Indique que l'objet a été modifié                                                              |
|                      | - Supprimé : Indique que l'objet a été supprimé                                                            |
|                      | - Changement massif : Indique un changement massif de configuration sur des objets                         |
|                      | - Activé : Indique que l'objet a été activé                                                                |
|                      | - Désactivé : Indique que l'objet a été désactivé                                                          |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Type                 | Indique le type d'objet concerné                                                                           |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Objet                | Indique le nom de l'objet concerné                                                                         |
+----------------------+------------------------------------------------------------------------------------------------------------+
| Auteur               | Indique l'utilisateur ayant effectué cette modification                                                    |
+----------------------+------------------------------------------------------------------------------------------------------------+

En cliquant sur le nom d'un objet, vous pouvez visualiser l'historique des modifications réalisées sur ce dernier.

.. image :: /images/guide_exploitation/fobjectmodif.png
   :align: center

Le tableau ci-dessous définit les colonnes du tableau des modifications :

+----------------------+-----------------------------------------------------------+
|   Colonne            |  Description                                              |
+======================+===========================================================+
| Date                 | Date et heure de la modification                          |
+----------------------+-----------------------------------------------------------+
| Nom de l'utilisateur | Nom de la personne ayant réalisé la modification          |
+----------------------+-----------------------------------------------------------+
| Type                 | Type de modification                                      |
+----------------------+-----------------------------------------------------------+
|                      | La dernière colonne décrit la modification en elle-même : |
|                      |                                                           |
|                      | - Nom du champ : Décrit le champ du formulaire modifié    |
|                      | - Avant : Indique l'ancienne valeur                       |
|                      | - Après : Indique la nouvelle valeur                      |
+----------------------+-----------------------------------------------------------+

*************
Configuration
*************

Pour activer la journalisation des actions utilisateurs, rendez-vous dans le
menu **Administration > Paramètres > Options** et cocher la case
**Activer/Désactiver les journaux d'audit**:

.. image:: /images/guide_exploitation/logs_audit_enable.png
    :align: center
