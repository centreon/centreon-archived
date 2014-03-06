.. _notifications_escalation:

==============================
Les escalades de notifications
==============================

**********
Définition
**********

D'une manière générale, en cas de déclenchement d'une alerte, une notification permet de contacter un ou plusieurs contacts (ou groupes de contacts). 
De même, il est possible d'envoyer plusieurs notifications suivant un intervalle de temps régulier.

Une escalade de notifications permet de contacter différents groupes de contacts au fil des notifications envoyées ou de changer le moyen de notification (remplacer les mails par un SMS).
La définition d'une escalade de notification pour un hôte, un groupe d'hôte, un service, un groupe de services ou un méta-service écrase la configuration classique des notifications pour cet objet.

Exemple : Un service A est paramétré pour envoyer des notifications à un groupe de contact "A" en cas de statut non-OK. Ces notifications sont envoyées toutes les 5 minutes.
Si pendant un certain nombre de notifications envoyées le statut du service est toujours en non-OK, il est possible de contacter les individus du groupe de contact "B" etc...

Les escalades de notifications sont pratiques dans les cas où il existe dans une société une équipe de support de niveau 1, niveau 2, niveau 3...
Lorsqu'un problème survient l'équipe de support niveau 1 est contactée. Si pendant un certain temps l'équipe niveau 1 n'a pas réussi à résoudre le problème, l'équipe niveau 2 est avertie etc...

*************
Configuration
*************

Pour ajouter une escalade de notification :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/04notificationsescalation.png
   :align: center 

* Les champs **Nom d'escalade** et **Alias** permettent de définir un nom et un alias à l'escalade de notifications.
* Le champ **Première notification** permet de choisir le numéro de la notification à partir de laquelle le groupe de contact sera averti.
* Le champ **Dernière notification** permet de choisir le dernier numéro de la notification pour lequel ce groupe de contact sera averti. Si le groupe de contact est le dernier niveau de l'escalade. La valeur de ce champ est **0**.
* Le champ **Intervalle de notification** définit l'intervalle de notifications entre chaque alerte.
* Le champ **Période d'escalade** définit la période temporelle de notifications.
* Les champs **Options d'escalade des hôtes** et **Options d'escalade des services** définissent les statuts d'hôte et de service pour laquelle l'escalade est utilisée.
* La liste **Groupes de contacts liés** définit le groupe de contact à contacter lors du déclenchement de l'escalade.
* Le champ **Commentaire** permet de commenter l'escalade.

Application de l'escalade
=========================

Pour sélectionner les différents objets qui seront concernés par cette escalade, les onglets
**Escalade des hôtes**, **Escalade des services**, **Escalade des groupes d'hôtes**, **Escalade des méta-services**, **Escalade des groupes de services**
permettent de choisir les objets sur lesquels les escalades sont appliquées.
