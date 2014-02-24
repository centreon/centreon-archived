===========
Les modèles
===========

**********
Définition
**********

Un modèle est une pré-configuration de paramètres d'un objet qui pourra être utilisé pour configurer ce dernier.
Le principal avantage est de pouvoir définir des valeurs par défaut pour certains objets afin d'accélérer la création d'objets similaires.

Lors de la création d'un modèle, seul le nom du modèle est obligatoire. Les autres attributs sont optionnels.

Il existe trois types de modèles :

*	Les modèles d'hôtes
*	Les modèles de services
*	Les modèles de contacts

Les avantages sont :

*   Définition simplifiée des éléments
*   Pas de redondance d'information
*   Facilité d'ajout de nouvelles ressources
*   Configurations prédéfinies assimilées à un « catalogue d'indicateurs»
*   Les modèles peuvent hériter d'autres modèles

*******************
Les modèles d'hôtes
*******************

Héritage
========

Un hôte ou un modèle d'hôte peut hériter d'un ou plusieurs modèles d'hôtes. Cet héritage peut être :

*   de type associatif (addition de plusieurs modèles d'hôte)
*   de type père-fils

Héritage de type Père-Fils
~~~~~~~~~~~~~~~~~~~~~~~~~~

Il s'agit d'une prédéfinition de paramètres à "n" niveaux. L'objet hérite de son modèle qui peut lui même hériter de son modèle.
Si le fils redéfini un paramètre, ce dernier écrase celui défini dans les modèles de niveaux supérieurs. Sinon il vient compléter le paramétrage.

Héritage de type associatif
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Il s'agit d'additionner plusieurs modèles au sein d'un même objet afin d'additionner l'ensemble des paramètres disponibles.
Si un hôte hérite de plusieurs modèles d'hôtes et si un même paramètre est défini sur plusieurs modèles, alors le modèle d'hôte situé au dessus des autres modèles est prioritaire par rapport à ses ascendants.

[ TODO Schéma explicatif : images/01.png]

Le schéma ci-dessous présente un hôte héritant de plusieurs modèles d'hôtes.

[ TODO Schéma explicatif : images/02.png]

Configuration
=============

Pour ajouter un modèle d'hôtes :

#. Rendez-vous dans le menu **Configuration** ==> **Hôtes**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

***********************
Les modèles de services
***********************

Héritage
========

Un service ou un modèle de service ne peut hériter que d'un seul modèle de service (héritage de type Père-Fils).

[ TODO Schéma explicatif : images/03.png]

Configuration
=============

Pour ajouter un modèle de services :

#. Rendez-vous dans le menu **Configuration** ==> **Services**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

********************
Les bonnes pratiques
********************

[TODO EXPLICATION A REVOIR]

Explications
============

La bonne pratique veut que des modèles de services soient associés à des modèles d'hôtes : lors de la création d'un hôte, les services sont générés automatiquement à partir des modèles d'hôtes.

Exemple : Je créé l'hôte webserver01.doccentreon.local selon le modèle ci-dessous :

[ TODO Schéma explicatif : images/04.png]

L'hôte webserver01.doccentreon.local aura automatiquement les services suivants générés :

*	Le service Ping permettra de valider la présence de l'hôte sur le réseau via un PING
*	Le service HTTP-Port permettra de vérifier la disponibilité du port 80
*	Le service HTTPS-Port permettra de vérifier la disponibilité du  port 443
*	Le service CPU permettra de vérifier la consommation CPU de la machine
*	Le service RAM permettra de vérifier la consommation de la mémoire vive
*	Le service Disk-C permettra de vérifier la consommation de la partition C:

Lorsque les services d'un hôte sont générés à partir des modèles d'hôtes, il est possible que certains services générés ne soient plus ou pas vérifiés par l'outil de supervision.
Dans ce cas, il est nécessaire de désactiver les services inutilisés (et non de les supprimer).
En cas de suppression des services, la regénération des services de l'hôte à partir des modèles d'hôtes recréera les services supprimés.

Configuration
=============

La liaison des modèles de services avec les modèles d'hôtes a lieu dans l'onglet **Relations** des modèles de services ou des modèles d'hôtes.

***********************
Les modèles de contacts
***********************

Un contact ou un modèle de contact peut hériter d'un seul modèle de contacts.

[ TODO Schéma explicatif : images/05.png]

Configuration
=============

Pour ajouter un modèle de contacts :

#. Rendez-vous dans le menu **Configuration** ==> **Utilisateurs**
#. Dans le menu de gauche, cliquez sur **Contact Templates**  [ TODO Pas de traduction disponible]
#. Cliquez sur **Ajouter**

**Remarque** : les modèles de contacts sont utilisés pour l'import automatique de profils via un annuaire LDAP [TODO rajouter lien/reférence vers IMPORT LDAP].
