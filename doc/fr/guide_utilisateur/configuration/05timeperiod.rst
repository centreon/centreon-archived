========================
Les périodes temporelles
========================

**********
Définition
**********

Une période temporelle est la définition d'un intervalle de temps pour chacun des jours de la semaine.
Ces périodes temporelles servent à activer les fonctionnalités de l'ordonnanceur sur une période donnée.

Les périodes temporelles s'appliquent à deux types d'actions :

*	L'exécution des commandes de vérification
*	L'envoi de notifications

*************
Configuration
*************

La configuration des périodes temporelles se déroule dans le menu **Configuration** ==> **Utilisateurs** ==> **Périodes temporelles**.

Options basiques
================

*	Les champs **Nom de la période temporelle** et **Alias** définissent respectivement le nom et la description de la période temporelle.
*	Les champs appartenant à la sous-catégorie **Période de temps** définissent les jours de la semaine pour lesquels il est nécessaire de définir des plages horaires.
*	Le tableau **Exceptions** permet d'inclure des jours exclus de la période de temps

Syntaxe d'une période de temps
==============================

Lors de la création d'une période temporelle, les caractères suivants permettent de définir les périodes temporelles :

*	Le caractère ":" sépare les heures des minutes. Exemple : HH:MM
*	Le caractère "-" indique une continuité entre deux horaires
*	Le caractère "," permet de séparer deux plages horaires

Quelques exemples :

*	24 heures sur 24 et 7 jours sur 7 jours : **00:00-24:00** (à appliquer à tous les jours de la semaine).
*	De 08h00 à 12h00 et de 14h00 à 18h45 les jours de la semaine :  **08:00-12:00,14:00-18:45** (à entrer au niveau des jours de la semaine uniquement).

.. image :: /images/guide_utilisateur/configuration/05timeperiod.png
   :align: center 

Les exceptions
==============

Les exceptions permettent d'inclure à la plage temporaire des jours exceptionnels (surcharge de la définition du fonctionnement régulier de la journée).

Exemple : Un administrateur souhaite définir une période temporelle qui regroupe les heures de fermeture du bureau c'est à dire :

*	De 18h00 à 07h59 les jours de semaines
*	24 heures sur 24 les weekend
*	Les jours fériés, jours de fermeture exceptionnelle

Afin de pouvoir définir les jours fériés ainsi que les jours de fermeture exceptionnelle, il est nécessaire d'utiliser les exceptions.

Pour ajouter une exception, cliquez sur le bouton |navigate_plus|.
Par journée exceptionnelle, vous devez définir une plage horaire. Le tableau ci-dessous présente quelques exemples possibles :

+-----------------------+-------------------------+-----------------------------------------------------------------+
|         Jour(s)       |    Période de temps     |                            Explications                         |
+=======================+=========================+=================================================================+
|     1 january         |       00:00-24:00       |   Toute la journée le premier janvier de chaque année           |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     2014-02-10        |       00:00-24:00       |   Toute la journée du 10 février 2014                           |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|  1 july - 1 august    |       00:00-24:00       |   Tous les jours de chaque année du 1 juillet au 1 août         |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     30 november       |       08:00-19:00       |   De 08h00 à 19h00 tous les 30 novembre de chaque année         |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|      day 1 - 20       |       00:00-24:00       |   Toute la journée du premier au 20 de chaque mois              |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     saturday -1       | 08:00-12:00,14:00-18:45 |   Tous les derniers samedi du mois les heures ouvrées           |
+-----------------------+-------------------------+-----------------------------------------------------------------+
|     monday -2         |       00:00-24:00       |   Tous les avant derniers lundi du mois toute la journée        |
+-----------------------+-------------------------+-----------------------------------------------------------------+

Options avancées
================

Au sein des options avancées, il est possible d'**inclure** ou d'**exclure** des périodes à la période temporelle.
Exemple d'application. Prenons deux périodes temporelles :

*	Une qui est définie 24 heures sur 24 / 7 jours sur 7 appelé **24x7**
*	Une autre qui regroupe les horaires d'ouvertures du bureau appelé **working_hours**

Pour obtenir les horaires de fermeture du bureau, je n'ai qu'à créer une période temporelle dans laquelle j'inclus la plage **24x7** et pour laquelle j'exclus la plage **working_hours**.

.. |navigate_plus|	image:: /images/navigate_plus.png
