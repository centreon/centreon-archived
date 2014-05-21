=============================
Les temps d'arrêts récurrents
=============================

**********
Définition
**********

Un temps d'arrêt est une période de temps durant laquelle les notifications sont désactivées pour un hôte ou un service.
Les temps d'arrêts sont pratiques lors d'opérations de maintenance sur un hôte ou un service : ils permettent d'éviter de recevoir des alertes de type faux-positif.

Les temps d'arrêts récurrents sont des temps d'arrêts qui reviennent de manière répétitive. 

Exemple : Une sauvegarde des machines virtuelles est effectuée tous les jours de 20h00 à minuit. Ce type de sauvegarde a tendance à saturer l'utilisation CPU de toutes les machines virtuelles.
Il est nécessaire de programmer des temps d'arrêts récurrents sur les services concernés afin d'éviter de recevoir des notifications de 20h00 à minuit.

.. note::
   Les temps d'arrêts sont pris en comptes dans le calcul du taux de disponibilité de la ressource dans le menu "Tableau de bord".

***************************
Les types de temps d'arrêts
***************************

Il existe deux types de temps d'arrêts :

* Les temps d'arrêts **fixe** : C'est à dire que le temps d'arrêt a lieu exactement pendant la période de temps définie.
* Les temps d'arrêts **flexible** : C'est à dire que si pendant la période de temps définie le service ou l'hôte retourne un statut non-OK alors le temps d'arrêt dure un certain nombre de secondes (à définir dans le formulaire) à partir du moment où l'hôte ou le statut a retourné un statut non-OK.

*************
Configuration
*************

Pour ajouter un temps d'arrêt récurrent :

#. Rendez-vous dans le menu **Configuration** ==> **Hôtes** (ou **Services** suivant le type d'objet sur lequel réaliser le temps d'arrêt)
#. Dans le menu de gauche, cliquez sur **Temps d'arrêt**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/05recurrentdowntimes.png
   :align: center 

Configuration des temps d'arrêts
================================

* Les champs **Nom** et **Description** permettent de donner un nom et de décrire le temps d'arrêt récurrent.
* Le champ **Activer** permet d'activer ou de désactiver le temps d'arrêt.
* Le champ **Période** permet de définir une ou plusieurs périodes de temps d'arrêt récurrent. Pour ajouter une période, cliquez sur le symbole |navigate_plus|.

Il est possible de choisir trois types de périodes :

* Hebdomadaire : Permet de choisir les jours de semaine
* Mensuel : Permet de choisir les jours dans un mois
* Date spécifique : Permet de choisir des dates spécifiques

* Le champ **Jours** définit le (ou les) jour(s) concerné(s).
* Le champ **Période de temps** contient la période de temps concernée (exprimée en HH:MM - HH:MM).
* Le champ **Type de temps d'arrêt** définit le type de temps d'arrêt souhaité.

.. note::
   Il est possible de combiner plusieurs types de périodes au sein d'un seul temps d'arrêt.

Relations
=========

* La liste **Lié aux hôtes** permet de choisir le ou les hôtes concernés par le temps d'arrêt récurrent.
* Si un groupe d'hôte est choisi avec la liste **Lié avec le groupe d'hôtes** tous les hôtes appartenant à ce groupe sont concernés par le temps d'arrêt récurrent.
* La liste **Lié avec les services** permet de choisir le ou les services concernés par le temps d'arrêt récurrent.
* Si un groupe de services est choisi avec la liste **Linked with Service Groups** tous les services appartenant à ce groupe sont concernés par le temps d'arrêt récurrent.

.. |navigate_plus|  image:: /images/navigate_plus.png
