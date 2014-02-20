===============
Les dépendances
===============

********
Principe
********

Les dépendances sont utilisées afin de répondre à deux besoins :

* Limiter l'envoi de notifications

Exemples :

Si un hôte est indisponible, il est nécessaire d'envoyer une notification pour l'hôte indisponible mais l'envoi de notifications pour les services liés à cette hôte est automatiquement désactivé (étant donné que l'hôte ne peut pas être interrogé).

Si un service est vérifié via le protocole SNMP, si l'agent SNMP de la machine cible est indisponible alors ce service ne peut pas être joint. Une seule notification doit être envoyée : celle pour l'agent SNMP indisponible.

* Mettre en place une hiérarchie entre les hôtes et les services. Un switch est connecté à un hôte. Par conséquent, l'hôte est dépendant de ce switch. Si le switch devient indisponible, alors l'hôte lié à celui-ci est injoignable.

* Limiter la vérification et l'envoi de notifications nécessaires

Exemple :
Prenons le cas d'un cluster d'hôtes actif/passif. L'hôte A est actif et l'hôte B est passif.
Les services sont démarrés sur l'hôte A, si l'hôte A devient indisponible alors les services sont démarrés sur l'hôte B.

Notre outil de supervision doit être capable de raisonner de la manière suivante : si l'hôte A est disponible alors l'hôte B n'est pas vérifié (il est également possible de laisser la vérification de l'hôte B tout en désactivant les notifications liés à cet hôte). 
Si l'hôte A devient indisponible alors l'hôte B est vérifié.

********************************************
Gestion des dépendances avec Centreon Broker
********************************************

[ TODO Besoin d'informations : Ou bien voir en dessous j'ai parlé de Centreon Broker pour les champs spécifiques]

*********
Les hôtes
*********

La gestion simple
=================

Au sein de l'onglet **Relations** d'une fiche de configuration d'hôte (**Configuration** ==> **Hôtes** ==> **Ajouter**) il est possible de définir deux paramètres :

* Les hôtes parents : signifie que les hôtes sélectionnés sont parents de l'hôte. Si tous les hôtes parents sélectionnés deviennent indisponible ou injoignable alors l'hôte sera injoignable.

Exemple : Un hôte est connecté à un switch. Si ce switch tombe en panne, l'hôte n'est plus joignable.

* Les hôtes enfants : signifie que l'hôte devient parent de tous les hôtes enfants sélectionnés

Le principal défaut de cette méthode de configuration est que les hôtes parents et les hôtes enfants doivent être supervisés par le même collecteur de supervision.

La gestion avancée
==================

Il est possible de gérer la dépendance entre les hôtes d'une manière plus intelligente. Pour cela :

#. Rendez-vous dans **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Hôtes**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Dans ce cas, nous avons deux types d'hôtes qui entrent en jeu : un ou des hôtes (appelé hôtes maitres) dont le statut contrôle l'exécution et les notifications d'autres hôtes (appelés hôtes dépendants).
Si vous utilisez Centreon Broker, il est également possible à partir des hôtes maitres de contrôler l'exécution et les notifications de services (appelés services dépendants)

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances de l'hôte maitre deviennent indisponibles, la dépendance en cours de création n'est plus pris en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des hôtes maitres qui empêcheront la vérification des hôtes ou des services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maitres qui empêcheront l'envoi de notifications pour les hôtes ou les services dépendants
* La liste **Nom d'hôtes** définie le ou les hôtes maitres
* La liste **Nom d'hôtes liés** définie les hôtes dépendants
* La liste **Services dépendants** définie les services dépendants
* Le champ **Commentaire** permet de commenter la dépendance

************
Les services
************

Pour ajouter une dépendance au niveau des services :

#. Rendez-vous dans **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Services**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Dans ce cas, nous avons deux entités qui entrent en jeu : les services (dits maitres) qui contrôlent l'exécution et les notifications d'autres services (dits dépendants).
Si vous utilisez Centreon Broker, il est également possible de contrôler l'exécution et les notifications d'autres hôtes.

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du service maitre deviennent indisponibles la dépendance en cours de création n'est plus pris en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des services maitres qui empêcheront la vérification des hôtes ou des services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des services maitres qui empêcheront l'envoi de notifications pour les hôtes ou les services dépendants
* La liste **Services** définie le ou les services maitres
* La liste **Services dépendants** définie les services dépendants
* La liste **Hôtes dépendants** définie les hôtes dépendants
* Le champ **Commentaire** permet de commenter la dépendance

***********
Les groupes
***********

Les groupes d'hôtes
===================

Pour ajouter une dépendance au niveau des groupes d'hôtes :

#. Rendez-vous dans **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Groupes d'hôtes**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Deux types de groupes d'hôtes : Un groupe d'hôtes est dit maitre s'il contrôle l'exécution et la notification d'autres groupes d'hôtes (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du groupe d'hôte maitre deviennent indisponibles la dépendance en cours de création n'est plus pris en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des groupes d'hôtes maitres qui empêcheront la vérification des groupes d'hôtes dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maitres qui empêcheront l'envoi de notifications pour des groupes d'hôtes dépendants
* La liste **Nom du groupe d'hôte** définie le ou les groupes d'hôtes maitres
* La liste **Nom des groupes d'hôtes liés** définie le ou les groupes d'hôtes dépendants
* Le champ **Commentaire** permet de commenter la dépendance

Les groupes de services
=======================

Pour ajouter une dépendance au niveau des groupes de services :

#. Rendez-vous dans **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Groupes de services**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Deux types de groupes de services : Un groupe de services est dit maitre s'il contrôle l'exécution et la notification d'autres groupes de services (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du groupe de service maitre deviennent indisponibles la dépendance en cours de création n'est plus pris en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des groupes de services maitres qui empêcheront la vérification des groupes de services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maitres qui empêcheront l'envoi de notifications pour des groupes de services dépendants
* La liste **Nom des groupes de services** définie le ou les groupes de services maitres
* La liste **Nom des groupes de services liés** définie le ou les groupes de services dépendants
* Le champ **Commentaire** permet de commenter la dépendance

*****************
Les méta-services
*****************

Pour ajouter une dépendance au niveau des méta-services :

#. Rendez-vous dans **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Méta-services**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Deux types de méta-services : Un méta-service est dit maitre s'il contrôle l'exécution et la notification d'autres méta-services (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du méta-service maitre deviennent indisponibles la dépendance en cours de création n'est plus pris en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des méta-services maitres qui empêcheront la vérification des méta-services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des méta-services qui empêcheront l'envoi de notifications pour des méta-services dépendants
* La liste **Nom du méta-service** définie le ou les méta-services maitres
* La liste **Nom des méta-services liés** définie le ou les méta-services dépendants
* Le champ **Commentaire** permet de commenter la dépendance
