===========
Les modèles
===========

**********
Définition
**********

Un modèle peut être comparé à un moule de configuration pour différents objets.
Il contient une pré-configuration pour un objet le principal avantage est de pouvoir définir des valeurs par défaut pour certains objets afin d'accélérer la création d'objets similaires.

Lors de la création d'un modèle, il n'y a que le nom du modèle qui est obligatoire. Le reste des attributs reste optionnel.

Il existe trois types de modèles :

*	Les modèles d'hôtes
*	Les modèles de services
*	Les modèles de contacts

*******************
Les modèles d'hôtes
*******************

Héritage
--------

Un hôte ou un modèle d'hôte peut hériter d'un ou plusieurs modèles d'hôtes.
Si un hôte hérite de plusieurs modèles d'hôtes, alors le modèle d'hôte situé au dessus des autres modèles est prioritaire par rapport à ses ascendants.

[ TODO Schéma explicatif : images/01.png]

Le schéma ci-dessous présente un hôte héritant de plusieurs modèles d'hôtes.

[ TODO Schéma explicatif : images/02.png]

Configuration
-------------

Pour ajouter un modèle d'hôtes :

#. Rendez-vous dans **Configuration** ==> **Hôtes**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

***********************
Les modèles de services
***********************

Héritage
--------

Un service ou un modèle de service ne peut hériter que d'un seul modèle de service.

[ TODO Schéma explicatif : images/03.png]

Configuration
-------------

Pour ajouter un modèle de services :

#. Rendez-vous dans **Configuration** ==> **Services**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

********************
Les bonnes pratiques
********************

Explications
------------

La bonne pratique veut que des modèles de services soient associés à des modèles d'hôtes : lors de la création d'un hôte, les services sont générés automatiquement à partir des modèles d'hôtes.

Exemple : Je créé l'hôte webserver01.doccentreon.local selon le modèle ci-dessous :

[ TODO Schéma explicatif : images/04.png]

L'hôte webserver01.doccentreon.local aura automatiquement les services suivants générés :

*	Le service Ping permettra de pinger l'hôte
*	Le service HTTP-Port permettra de vérifier le port 80
*	Le service HTTPS-Port permettra de vérifier le port 443
*	Le service CPU permettra de vérifier la consommation CPU de la machine
*	Le service RAM permettra de vérifier la consommation de la mémoire vive
*	Le service Disk-C permettra de vérifier la consommation de la partition C

Lorsque les services d'un hôte sont générés à partir des modèles d'hôtes, il est possible que certains services générés ne soient plus ou pas vérifiés par l'outil de supervision.
Dans ce cas, il est nécessaire de désactiver les services inutilisés (et non de les supprimer).
En cas de suppression des services, la regénération des services de l'hôte à partir des modèles d'hôtes recréera les services supprimés.

Configuration
-------------

La liaison des modèles de services avec les modèles d'hôtes a lieu dans l'onglet **Relations** des modèles de services ou des modèles d'hôtes.

***********************
Les modèles de contacts
***********************

Un contact ou un modèle de contact peut hériter d'un seul modèle de contacts.

[ TODO Schéma explicatif : images/05.png]

Configuration
-------------

Pour ajouter un modèle de contacts :

#. Rendez-vous dans **Configuration** ==> **Utilisateurs**
#. Dans le menu de gauche, cliquez sur **Contact Templates**  [ TODO Pas de traduction disponible]
#. Cliquez sur **Ajouter**