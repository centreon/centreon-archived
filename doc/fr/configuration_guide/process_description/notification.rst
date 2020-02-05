===========================================
Le processus de notifications dans Centreon
===========================================

*********************************
Notifier un contact dans Centreon
*********************************

Avant qu'un contact soit notifié au sein de Centreon, il est nécessaire de respecter plusieurs étapes.
Si aucune escalade de notification n'est définie, le processus de gestion des notifications est standard. Celui-ci est décrit ci-dessous :

1. Un service (ou un hôte) est vérifié à intervalle régulier en fonction de la période temporelle de vérifications définie pour lui (Dans le cas d'un service passif, on attend que le statut du service change d'état)
2. Lorsqu'une anomalie survient (statut non-OK), le service (ou l'hôte) passe en état SOFT
3. Après que le nombre maximum de vérifications avant validation de l'état ait eu lieu et si le service (ou l'hôte) persiste en conservant son statut non-OK son état passe de SOFT à HARD. Le moteur de supervision met en cache le numéro de la notification pour le service (ou l'hôte) : c'est à dire 0.

A chaque intervalle d'envoi de notification pour le service (ou l'hôte) et jusqu'à la fin du statut non-OK, le moteur de supervision réalise les opérations suivantes :

4. Le moteur de supervision vérifie que la période temporelle de notifications définie pour le service (ou l'hôte) permet la notification à l'instant où le service (ou l'hôte) est passé en état HARD. Si oui, alors on passe à l'étape suivante sinon, on attend que la période temporelle définie pour le service (ou l'hôte) permette la notification.
5. Le moteur de supervision vérifie que la notification est activée pour le statut actuel du service (ou de l'hôte)

Pour chaque contact associé au service (ou à l'hôte) :

6. Le moteur de supervision vérifie plusieurs paramètres :

* Est-ce que les notifications sont activées pour ce contact ?
* Est-ce que la période temporelle de notifications définie pour le contact permet la notification ?
* Est-ce que le contact est configuré pour être notifié pour le statut actuel du service (ou l'hôte) ?

7. Si ces trois conditions sont validées, alors le moteur de supervision alerte le contact en utilisant le script de notifications définit pour le service ou l'hôte.
8. Le moteur de supervision incrémente le numéro de notification de 1

Le schéma ci-dessous résume la gestion des notifications au sein de Centreon :

.. image :: /images/guide_exploitation/hnotifications_schema.png
   :align: center
   
**************************************************
Les escalades de notifications au sein de Centreon
**************************************************

Les escalades de notifications permettent deux choses :

* Notifier des contacts différents en fonction du nombre de notifications envoyées
* Changer de moyens de notifications au cours du temps

En cas d'utilisation des escalades de notifications, la récupération de la liste de contact est quelque peu différente :

#. Un service (ou un hôte) est vérifié à intervalle régulier en fonction de la période temporelle de vérification définie pour lui
#. Lorsqu'une anomalie survient (statut non-OK), le service (ou l'hôte) passe en état SOFT
#. Après que le nombre maximum de vérifications avant validation de l'état ait eu lieu, si le service (ou l'hôte) persiste en conservant son statut non-OK son état passe de SOFT à HARD. Le moteur de supervision met en cache le numéro de la notification pour le service (ou l'hôte) : c'est à dire 0.

A chaque intervalle d'envoi de notification pour le service (ou l'hôte) et jusqu'à la fin du statut non-OK le moteur de supervision réalise les opérations suivantes :

1. Si aucune escalade de notification n'est définie pour le service (ou l'hôte) et le numéro actuel de notification, alors le traitement de la notification est fait de la même manière que pour une notification classique : le moteur de supervision utilise la configuration de notifications définie pour le service (ou l'hôte).

2. Si une escalade de notification est définie pour le service (ou l'hôte) et le numéro actuel de notification, alors le moteur de supervision se base sur la configuration de l'escalade afin de sélectionner les contacts à notifier et les moyens à utiliser.

3. Le mécanisme de traitement d'une notification est le même que pour l'envoi d'une notification normale

Pour rappel, la configuration des escalades de notifications est définie dans le chapitre :ref:`Les escalades de notifications <notifications_escalation>`.
