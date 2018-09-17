.. _categoriesandgroups:

===================================
Gérer les groupes et les catégories
===================================

Au sein de Centreon, il est possible de regrouper un ou plusieurs objets au sein de différents groupes :

* :ref:`Les groupes d'hôtes <hostgroups>`
* :ref:`Les groupes de services <servicegroups>`
* :ref:`Les groupes de contacts <contactgroups>`

Il est également possible de créer des catégories :ref:`d'hôtes <hostcategory>` ou de :ref:`services <servicecategory>`.

***********
Les groupes
***********

D'une manière générale, les groupes sont des containeurs permettant de regrouper un ensemble d'objet possédant une propriété commune : 

* Même identité matérielle (serveurs Dell, HP, IBM, ...), identité logique (équipements réseau) ou identité géographique (Europe, Asie, Afrique, Amérique du nord, ...)
* Appartenance à une même application (application CMS, ...) ou à un même secteur d'activité (Gestion de la paie, ...)
* ...

Les groupes d'hôtes et de services
==================================

Les groupes d'hôtes et de services permettent de regrouper des objets par entités logiques. Ils sont utilisés pour :

* La configuration des ACLs afin de lier un ensemble de ressources à un type de profil
* Permettre de visualiser les rapports de disponibilité par groupe. Générer un rapport de disponibilité des ressources "Agence Paris".
* Permettre de visualiser le statut d'un ensemble d'objets en sélectionnant dans les filtres de recherche un groupe d'objets
* Rechercher rapidement un à plusieurs graphiques de performances en parcourant l'arbre des objets par groupes puis par ressource

D'une manière générale, on cherche à regrouper les hôtes par niveau fonctionnel. Exemple : Hôtes DELL, HP ou encore Hôtes Linux, Windows...
On cherche également à regrouper les services par applications métiers. Exemple : Application de gestion de la paie, Application ERP, ...

.. note::
    Pour les hôtes appartenant à un groupe d'hôtes, la rétention des fichiers RRD peut être définie au sein du groupe d'hôtes auquel il appartient. Cette définition vient surcharger la définition globale. Dans le cas où un même hôte appartient à plusieurs groupes possédant chacun une définition de rétention, la valeur la plus élevée sera sélectionnée pour l'hôte.

Les groupes de contacts
=======================

Les groupes de contacts sont utilisés pour pouvoir notifier des contacts :

* Lors de la définition d'un hôte ou d'un service
* Lors de la définition d'une escalade de notifications

De plus, les groupes de contacts sont également utilisés lors de la définition d'un groupe d'accès.

Par conséquent, il est nécessaire de regrouper les contacts d'une manière logique. La plupart du temps, ils sont regroupés suivant leurs rôles au sein du système d'informations. Exemple : DSI, Administrateurs Windows, Administrateurs Linux, Responsable de l'application de Gestion de la paie, ...

.. _categoriesexplanation:

**************
Les catégories
**************

D'une manière générale, les catégories servent soit à définir un niveau de criticité pour un hôte ou un service, soit à regrouper techniquement un ensemble d'objets (services liés à une exécution de requête sur un SGBD MariaDB, ...).
La bonne pratique demande à ce qu'on regroupe des hôtes ou des services au sein de catégories pour pouvoir faciliter le filtrage de ces objets au sein d'ACL.
Les catégories sont également utilisées pour définir des types d'objets au sein du module Centreon MAP ou pour classer les objets au sein de sous-groupes dans le module Centreon BI.
