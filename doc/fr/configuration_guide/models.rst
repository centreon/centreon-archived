.. _hosttemplates:

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
Si un hôte hérite de plusieurs modèles d'hôtes et si un même paramètre est défini sur plusieurs modèles, alors le modèle d'hôte situé au-dessus des autres modèles est prioritaire par rapport à ses ascendants.

.. image :: /images/guide_utilisateur/configuration/09hostmodels.png
   :align: center

Le schéma ci-dessous présente un hôte héritant de plusieurs modèles d'hôtes.

.. image :: /images/guide_utilisateur/configuration/09hostmodelsheritage.png
   :align: center

Configuration
=============

Pour ajouter un modèle d'hôtes :

#. Rendez-vous dans le menu **Configuration > Hôtes**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

.. note::
    Se rapporter au chapitre de configuration des :ref:`hôtes<hostconfiguration>` pour configurer un modèle car le formulaire est identique.

.. note::
   Par défaut, les modèles d'hôte verrouillés sont masqués. Cocher la case "Eléments verrouillés" pour les afficher tous.

***********************
Les modèles de services
***********************

Héritage
========

Un service ou un modèle de service ne peut hériter que d'un seul modèle de service (héritage de type Père-Fils).

.. image :: /images/guide_utilisateur/configuration/09heritageservice.png
   :align: center

Configuration
=============

Pour ajouter un modèle de services :

#. Rendez-vous dans le menu **Configuration > Services**
#. Dans le menu de gauche, cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

.. note::
    Se rapporter au chapitre de configuration des :ref:`services<serviceconfiguration>` pour configurer un modèle car le formulaire est identique.

********************
Les bonnes pratiques
********************

Explications
============

La bonne pratique veut que des modèles de services soient associés à des modèles d'hôtes : lors de la création d'un hôte, les services sont générés automatiquement à partir des modèles d'hôtes.
Il y a deux intérêts à lier les modèles de services aux modèles d'hôtes :

* Les services générés automatiquement conservent leur granularité : il est donc possible de modifier les attributs d'un service sans impacter les autres services issus de ce modèle
* La création de nouveaux hôtes est grandement accélérée : vous n'avez qu'à définir l'hôte et les modèles d'hôtes associés à celui-ci

Exemple : Je créé l'hôte srvi-web-01 selon le modèle ci-dessous :

.. image :: /images/guide_utilisateur/configuration/09hostexemple.png
   :align: center

L'hôte srvi-web-01 possèdera automatiquement les services suivants :

* Load, CPU, Memoiry, disk-/ à partir des modèles de services issus du modèle d'hôte "Linux-Server-RedHat-5"
* broken-jobs, hit-ratio, tablespaces, listener à partir des modèles de services issus du modèle d'hôte "DB-MySQL"
* processus et connection à partir des modèles de services issus du modèle d'hôte "Web-Server-Apache"

Lorsque les services d'un hôte sont générés à partir des modèles d'hôtes, il est possible que certains services générés ne soient plus ou pas vérifiés par l'outil de supervision.
Dans ce cas, il est nécessaire de désactiver les services inutilisés (et non de les supprimer).
En cas de suppression des services, la régénération  des services de l'hôte à partir des modèles d'hôtes va recréer les services supprimés.

Configuration
=============

La liaison des modèles de services avec les modèles d'hôtes a lieu dans l'onglet **Relations** des modèles de services ou des modèles d'hôtes.

.. note::
   Par défaut, les modèles de service verrouillés sont masqués. Cocher la case "Eléments verrouillés" pour les afficher tous.

***********************
Les modèles de contacts
***********************

Un contact ou un modèle de contact peut hériter d'un seul modèle de contact.

.. image :: /images/guide_utilisateur/configuration/09contactmodel.png
   :align: center

Configuration
=============

Pour ajouter un modèle de contacts :

#. Rendez-vous dans le menu **Configuration > Utilisateurs**
#. Dans le menu de gauche, cliquez sur **Modèles de contacts**
#. Cliquez sur **Ajouter**

.. note::
    Se rapporter au chapitre de configuration des :ref:`contacts<contactconfiguration>`. De plus, les modèles de contacts sont utilisés pour l'import automatique de profils via un annuaire :ref:`LDAP<ldapconfiguration>`.
