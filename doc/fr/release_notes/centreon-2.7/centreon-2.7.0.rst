##############
Centreon 2.7.0
##############

Released December 17, 2015

La version 2.7.0 de Centreon Web est maintenant téléchargeable sur notre `portail <https://download.centreon.com>`_. La liste complète des changements opérés dans la version 2.7.0 sont ci-dessous : 

Améliorations et corrections
----------------------------

* Changement de la charte graphique pour être en accord avec le nouveau logo de Centreon
* Passage en design Flat (CSS + icones)
* Amélioration de la custom view : 
 * Ajout d'un mode édition ou visualisation
 * Alègement graphique des widgets afin de pouvoir en mettre plus sur une page
* Ajout d'un mode plein écran
* Revue des menus pour une amélioration de la navigation et une simplification des actions utilisateurs
* Refonte des pages dédiées hôtes et services dans le monitoring pour y intégrer plus d'informations
* Refonte graphique de la page de reporting
* Refonte des barres de recherches et des filtres dans chaque page de Centreon
* Refonte de la page des logs (suppression de la treeview + Ajout d'un système de recherche + Amélioration des performances)
* Refonte de la page des graphiques (suppression de la treeview + Ajout d'un système de recherche + ajout d'une pagination)
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
 * refonte de la partie exploitation 
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
* Les vues partagées la partie "custom views" ne sont plus automatiquement ajoutées dans les vues des utilisateurs. C'est aux utilisateurs de les charger lors de la création d'une vue à partir d'une liste de vues rendues publiques.

Corrections de sécurité
-----------------------

* Suppression des sessionID PHP dans les url des flux Ajax de certaines pages. 
* Intégration d'un tocken CSRF dans tous les formulaires afin d'éviter un effet "Man in the middle".

Fonctions supprimées
--------------------

* La compatibilité avec Nagios et NDOutils n'est plus effective sur Centreon web. Seuls Centreon Engine et Centreon Broker sont maintenant compatibles à partir de la version 2.7.0
* Suppression des exécutables centstorage et logAnalyser gérant la génération des graphiques et le stockage des logs avec NDOutils
* Suppression du module de chargement des configurations de Nagios dans Centreon.
* Suppression de la possibilité de configurer les couleurs de templates de graphiques
* Suppression des choix des couleurs pour les menus
* Suppression des choix des couleurs pour les statuts du monitoring
* Suppression de la possibilité de configurer les CGI de Nagios
* Transformation de la "tactical overview" en widget
* Transformation de la page des statuts des pollers en widget
* Suppression de la page de statut du serveur (PHPSysInfo) devenu non compatible avec la version cible de PHP conseillée pour Centreon
* Suppression des exclusions au niveau des "timeperiods" (les exclusions n'ont jamais fonctionné avec Centreon Engine 1.x et Nagios 3.x). Nous préférons ne pas laisser cette fonction dans l'interface. 

Problèmes connus
----------------
* La migration de la configuration des ACL d'accès aux pages de Centreon n'est pas complètement gérée durant le passage à la version 2.7.0. Ainsi, merci de vérifier vos configuration après la mise à jour. Les pages impactées sont : 
 * Monitoring > Hosts
 * Monitoring > Services
 * Monitoring > Performances (new page)
 * Monitoring > Downtimes
 * Monitoring > Comments
 * Monitoring > Eventlogs > System logs
* Le système de split des graphiques de performance ne fonctionne pas.
* La pagination peut ne pas fonctionner sur l'ensemble de l'application si nous nous baladons dans les x pages de la liste puis selectionnons la valeur maximum dans le selecteur du nombre de ligne. Cela provoque alors une page vide.
* Un problème lors de la migration bloque le système d'upgrade SQL : si des timeperiods ont été configurée dans le passé dans le système d'exclusion ou d'inclusion et ensuite supprimée, elle reste dans la base de données. Cela créé alors un blocage lors d'un ajout de contrainte sur une table MySQL.
  ::

  mysql> DELETE FROM timeperiod_exclude_relations WHERE timeperiod_id NOT IN (SELECT tp_id FROM timeperiod) OR timeperiod_exclude_id NOT IN (SELECT tp_id FROM timeperiod);


Comment l'installer ?
---------------------

Maintenant que vous avez pris connaissance de toutes spécificités de cette nouvelles version, vous pouvez l'installer. Si vous partez sur une installation depuis zero, reportez vous au :ref:`guide d'installation <install>`. 
Sinon si vous souhaitez mettre à jour une plateforme existante, veuillez vous référer au :ref:`guide de mise à jour <upgrade>`. Prenez soin de suivre scrupuleusement les pré-requis et les étapes de mise à jour afin de ne pas perdre de données durant votre mise à jour. 
