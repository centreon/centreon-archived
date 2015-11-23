##############
Centreon 2.7.0
##############

Risques
-------

* Problèmes de dépendances avec Centreon Engine et Centreon Broker
* Problèmes de mise à jour des schémas de base de données
* Les temporary et les failover sont définis par défaut sur Centreon Broker
* Problème de cache navigateur
* Problème avec des dépendances php (intl)
* Problème de compatibilité avec des modules installés
* Generation de conf qui ne se génère pas normalement
* Bascule direct de NDOutils vers Centreon Broker au passage de la version 2.7

Améliorations et corrections
----------------------------

* Changement de la charte graphiques pour être en accord avec le nouveau logo de Centreon
* Passage en design Flat (CSS + icones)
* Amélioration de la custom view : 
 * Ajout d'un mode édition ou visualisation
 * Alégement graphique des widgets afin de pouvoir en mettre plus sur une page
* Ajout d'un mode plein écran
* Revue des menus pour une amélioration de la navigation et une simplification des actions utilisateurs
* Refonte des pages dédiées hôtes et services dans le monitoring pour y intégrer plus d'informations
* Refonte graphique de la page de reporting
* Refonte des barres de recherches et des filtres dans chaque page de Centreon
* Refonte de la page des logs (suppresion de la treeview + Ajout d'un système de recherche + Amélioration des performances)
* Refonte de la page des graphiques (suppresion de la treeview + Ajout d'un système de recherche + ajout d'une pagination)
* Fusion des pages de downtimes pour les hôtes et les services
* Fusion des pages de commentaires pour les hôtes et les services
* Intégration d'un module graphique pour remplacer un composant QuickForm non performant (amélioration des formulaires sur la multiselection d'éléments)
* Simplification de la configuration de Centreon Broker (Temporary et Failover sont configurés automatiquement + les best practices améliorés)
* Amélioration de l'ergonomie de la configuration des objets : 
 * Amélioration du formulaire des hôtes
 * Amélioration du formulaire des services
 * Amélioration de la gestion des macros : système de formulaire dynamique qui propose les macros nécessaires héritées des templates pour un bon fonctionnement de la configuration
 * Ajout de la possibilité de mettre une description de chaque macro utilisée dans les commandes
 * Revue du cheminement pour la génération de la configuration
 * Création automatique d'une fichier de configuration pour l'ordonanceur lors de sa création
* Suppresion d'options de configuration dans la partie Administration, maintenant configurées automatiquement. Cela permet de simplifier la prise en main de Centreon
* Amélioration du système des ACL (Gain de performance)
* Intégration de Centreon CLAPI de manière native
* Amélioration de la documentaton : 
 * refonte la partie exploitation 
 * refonte de la partie user
 * intégration d'une partie API

Changements
-----------

* Changements graphiques / design importants de l'interface web n'assurant plus la compatibilité avec les anciens modules. Un travail de refactoring sera nécessaire pour garantir un fonctionnement optimal.
* Changement du système de timezone : gestion des DST (possible besoin de vérifier les timezones de chaque host et contact après la mise à jour)
* Changement du schéma de base de données pour les groupes de hôtes et groupes de services dans la base de données temps réel (storage) : ajout des ids et suppression d'informations telles que les alias, url, url note, icone.
* Changement du cheminement pour générer la configuration des instances Centreon Engine : plus de page spécifique afin de générer la configuration. L'action est accessible depuis le listing des pollers
* Passage en InnoDB de toutes les tables de Centreon (sauf data_bin et logs du fait de leur taille qui peut demander trop de temps de changement - Action Manuelle à faire suite à la migration).
* PHP 5.1 non supporté
* Compatibilité Browser IE 11, FF 5 et Chrome 39 minimum

Corrections de sécurité
-----------------------

* Suppression des sessionID PHP dans les url des flux Ajax de certaines pages. 
* Intégration d'un tocken CSRF dans tous les formulaires afin d'éviter un effe "Man in the middle".

Fonctions supprimées
--------------------

* Le support de Nagios et NDOutils n'est plus effectif sur Centreon web. Seuls Centreon Engine et Centreon Broker sont supportés à partir de la version 2.7.0
* Suppression des exécutables centstorage et logAnalyser gérant la génération des graphiques et le stockage des logs avec NDOutils
* Suppression du module de chargement des configurations de Nagios dans Centreon.
* Suppression de la possibilité de configurer les couleurs de templates de graphiques
* Suppression des choix des couleurs pour les menus
* Suppression des choix des couleurs pour les statuts du monitoring
* Suppression de la possibilité de configurer les CGI de Nagios
* Transformation de la tactical overview en widget
* Transformation de la page des statuts des pollers en widget
* Suppression de la page de statut du serveur (PHPSysInfo) devenu non compatible avec la version cible de PHP conseillée pour Centreon

Problèmes connus
----------------


.. toctree::
    :maxdepth: 1
