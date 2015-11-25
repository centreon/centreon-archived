.. _dependancy:

===============
Les dépendances
===============

********
Principe
********

Les dépendances sont utilisées afin de répondre à deux principaux besoins :

* Limiter l'envoi de notifications
* Cibler les alertes

Les dépendances d’objets sont de deux types :

* Dépendance **physique** entre objet : un switch de répartition est situé en amont d’un ensemble de serveurs et en aval d’un routeur
* Dépendance **logique** entre objet : l’accès à un site web avec authentification LDAP dépend de l’état de l’annuaire LDAP lui-même

*************************
Les dépendances physiques
*************************

Les dépendances physiques consistent à prendre en compte les liens physiques entre les équipements. Ce lien ne peut être défini que pour les objets de type "Hôte".

La configuration d'une dépendance physique se déroule au sein de l'onglet **Relations** d'une fiche de configuration d'un hôte (**Configuration** ==> **Hôtes** ==> **Ajouter**).

Il est possible de définir deux paramètres :

* Les hôtes parents : signifie que les hôtes sélectionnés sont parents de cet hôte (situé en amont). Si tous les hôtes parents sélectionnés deviennent indisponibles ou injoignables alors l'hôte sera considéré par l’ordonnanceur comme injoignable lui-même.
* Les hôtes enfants : signifie que l'hôte devient parent de tous les hôtes enfants sélectionnés.

.. note:: 
    Tous les parents d’un hôte doivent être dans un état non-OK pour que l’hôte lui-même soit considéré comme injoignable. A partir du moment où au moins un chemin d’accès (liaison de dépendance physique, alors l’ordonnanceur continuera de surveiller cet hôte.

Dans le cas où des relations de parentés ont été définies entre hôtes supervisés par des ordonnanceurs différents,  il est possible :

* D'empêcher l'établissement d'une relation de parenté, lors de la modification du formulaire d’hôte, entre deux hôtes supervisés par deux collecteurs différents.
* D'autoriser l'établissement de cette relation de parenté. Dans ce cas la dépendance ne sera pas gérée par les moteurs de supervision mais par Centreon Broker qui prendra en compte cette relation au sein de son moteur de corrélation.

Pour empêcher l'établissement de cette relation de parenté, il est nécessaire de cocher la case **Activer le mode strict de gestion des relations de parentés** au sein du menu **Administration** ==> **Options**.

A l'inverse si cette case n'est pas cochée alors les liens de parenté entre hôtes appartenant à deux collecteurs différents peuvent être établis.

.. note::
    Ne cochez pas le filtre de notification "Injoignable" sur les hôtes ainsi que sur les contacts pour ne pas recevoir ce type de notification.

************************
Les dépendances logiques
************************

Les dépendances logiques consistent à mettre en place des liens logiques entre plusieurs objets de différents types ou non.
Par exemple : Un service est chargé de superviser l'accès à une page web requérant une authentification basée sur un annuaire LDAP. Il est logique que si le serveur LDAP est en panne, l'accès à la page web sera limité voire impossible. Dans cette situation, la notification émise ne doit l'être que pour l'annuaire LDAP et non le site web.

Les hôtes
=========

Pour configurer une dépendance logique :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Hôtes**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/03hostdependance.png
   :align: center

Dans ce cas, nous avons deux types d'hôtes qui entrent en jeu : un ou des hôtes (appelé hôtes maîtres) dont le statut contrôle l'exécution et les notifications d'autres hôtes (appelés hôtes dépendants).
Si vous utilisez Centreon Broker, il est également possible à partir des hôtes maîtres de contrôler l'exécution et les notifications de services (appelés services dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances de l'hôte maître deviennent indisponibles, la dépendance en cours de création n'est plus prise en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des hôtes maîtres qui empêcheront la vérification des hôtes ou des services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maîtres qui empêcheront l'envoi de notifications pour les hôtes ou les services dépendants
* La liste **Nom d'hôtes** défini le ou les hôtes maîtres
* La liste **Nom d'hôtes liés** défini les hôtes dépendants
* La liste **Services dépendants** défini les services dépendants
* Le champ **Commentaire** permet de commenter la dépendance

Les services
============

Pour ajouter une dépendance au niveau des services :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Services**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/03servicedependance.png
   :align: center

Dans ce cas, nous avons deux entités qui entrent en jeu : les services (dits maîtres) qui contrôlent l'exécution et les notifications d'autres services (dits dépendants).
Si vous utilisez Centreon Broker, il est également possible de contrôler l'exécution et les notifications d'autres hôtes.

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du service maître deviennent indisponibles la dépendance en cours de création n'est plus prise en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du (ou des) service(s) maître(s) qui empêchera(ront) la vérification des hôtes ou des services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du (ou des) service(s) maître(s) qui empêchera(ront) l'envoi de notifications pour les hôtes ou les services dépendants
* La liste **Services** définie le ou les services maîtres
* La liste **Services dépendants** définie les services dépendants
* La liste **Hôtes dépendants** définie les hôtes dépendants
* Le champ **Commentaire** permet de commenter la dépendance

Les groupes d'hôtes
===================

Pour ajouter une dépendance au niveau des groupes d'hôtes :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Groupes d'hôtes**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/03hostgroupdependance.png
   :align: center

Deux types de groupes d'hôtes : Un groupe d'hôtes est dit maître s'il contrôle l'exécution et la notification d'autres groupes d'hôtes (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du groupe d'hôte maître deviennent indisponibles la dépendance en cours de création n'est plus prise en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des groupes d'hôtes maîtres qui empêcheront la vérification des groupes d'hôtes dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maîtres qui empêcheront l'envoi de notifications pour des groupes d'hôtes dépendants
* La liste **Nom du groupe d'hôte** définie le ou les groupes d'hôtes maîtres
* La liste **Nom des groupes d'hôtes liés** définie le ou les groupes d'hôtes dépendants
* Le champ **Commentaire** permet de commenter la dépendance

Les groupes de services
=======================

Pour ajouter une dépendance au niveau des groupes de services :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Groupes de services**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/configuration/10advanced_configuration/03servicegroupdependance.png
   :align: center

Deux types de groupes de services : Un groupe de services est dit maître s'il contrôle l'exécution et la notification d'autres groupes de services (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du groupe de service maître deviennent indisponibles la dépendance en cours de création n'est plus prise en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des groupes de services maîtres qui empêcheront la vérification des groupes de services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des hôtes maîtres qui empêcheront l'envoi de notifications pour des groupes de services dépendants
* La liste **Nom des groupes de services** définie le ou les groupes de services maîtres
* La liste **Nom des groupes de services liés** définie le ou les groupes de services dépendants
* Le champ **Commentaire** permet de commenter la dépendance

Les méta-services
=================

Pour ajouter une dépendance au niveau des méta-services :

#. Rendez-vous dans le menu **Configuration** ==> **Notifications**
#. Dans le menu de gauche, sous le titre **Dépendances**, cliquez sur **Méta-services**
#. Cliquez sur **Ajouter**

Deux types de méta-services : Un méta-service est dit maître s'il contrôle l'exécution et la notification d'autres méta-services (dit dépendants).

* Les champs **Nom** et **Description** indiquent le nom et la description de la dépendance
* Le champ **Relation de parenté** est à ignorer si vous utilisez Centreon Engine. S'il est activé, alors si les liens de dépendances du méta-service maître deviennent indisponibles la dépendance en cours de création n'est plus prise en compte.
* Le champ **Critères d'échec d'exécution** indique quels sont les statuts du ou des méta-services maîtres qui empêcheront la vérification des méta-services dépendants
* Le champ **Critères d'échec de notification** indique quels sont les statuts du ou des méta-services qui empêcheront l'envoi de notifications pour des méta-services dépendants
* La liste **Nom du méta-service** définie le (ou les) méta-service(s) maître(s)
* La liste **Nom des méta-services liés** définie le (ou les) méta-service(s) dépendant(s)
* Le champ **Commentaire** permet de commenter la dépendance
