===========
Les graphes
===========

**********
Définition
**********

Centreon permet de générer des graphiques à partir des informations de supervision. Il existe deux types de graphiques :

* Les graphiques de performances permettent de visualiser l'évolution des services de manière intuitive. Exemples : niveau de remplissage d'un disque dur, trafic réseau...
* Les graphiques d'historique (ou graphique des statuts) permettent de visualiser l'évolution des statuts d'un service.

Les graphiques de performances ont toujours comme abscisse une période de temps et comme ordonnée une mesure (Volts, Octets...).
Les graphiques d'historique ont toujours comme abscisse une période de temps, leurs ordonnées ne varient pas. Uniquement la couleur du graphique permet de visualiser le statut de l'objet :

* Vert pour le statut OK
* Orange pour le statut WARNING
* Rouge pour le statut CRITICAL
* Gris pour le statut UNKNOWN

Exemple de graphique de performances :

.. image :: /images/guide_utilisateur/graphic_management/01perf_graph.png
   :align: center 

Exemple de graphique d'historique :

.. image :: /images/guide_utilisateur/graphic_management/01stat_graph.png
   :align: center 

*************
Visualisation
*************

Les graphiques de performances
==============================

Il existe plusieurs manières de visualiser les graphiques de performances :

* Visualiser le graphique dans la liste des services (Menu **Supervision** ==> **Services**) en survolant l'icône |column-chart| 
* Visualiser le graphique dans le détail d'un service
* Se rendre dans le menu **Vues** ==> **Graphiques** pour visualiser un à plusieurs graphiques

Les graphiques d'historique
===========================

Comme pour les graphiques de performances, il existe dufférentes façons d'accéder au graphique d'historique :

* A partir de la page de détail d'un objet (voir le chapitre :ref:`Supervision temps-réelle <realtime_monitoring>`)
* A partir du menu **Vues** ==> **Graphiques**, en sélectionnant au préalable un service spécifique puis en cochant la case **Affichage de l'état**.

Visualiser plusieurs graphiques
===============================

Pour visualiser l'ensemble des graphiques, rendez-vous dans le menu **Vues** ==> **Graphiques**.

.. image :: /images/guide_utilisateur/graphic_management/01graph_list.png
   :align: center 

Le menu de gauche permet de sélectionner les hôtes et/ou les services pour lesquels on souhaite visualiser les graphiques.

La barre de recherche grise appelée **Période de visualisation** permet de sélectionner la période de temps pour laquelle on souhaite visualiser les graphiques.
La liste déroulante permet de sélectionner des périodes de temps prédéfinies. Il est possible de choisir manuellement la période de temps en utilisant les champs **Du** et **Au**, ce qui remplacera la sélection préfédinie.

Plusieurs actions sont possibles sur les graphiques :

* **Séparer les courbes** : sépare plusieurs courbes d'un graphique en plusieurs graphiques contenant chacun une courbe
* **Affichage de l'état** : affiche les graphiques d'historique liés aux graphiques de performances affichés

Pour exploiter les données des graphiques, il est possible de :

* Visualiser le graphique de performance sur un jour, une semaine, un mois et une année en cliquant sur le graphique de performances de votre choix
* De zoomer sur le graphique en cliquant sur l'icône |zoom|
* De sauvegarder le graphique en cliquant sur l'icône |save|
* De télécharger l'ensemble des données qui composent le graphique au format .csv en cliquant sur l'icône |text_binary_csv|

Filtres
-------

Il est possible de filtrer la sélection des ressources via :

* La barre de recherche rapide en recherchant par **hôte** ou **service**
* En parcourant l'arbre de sélection (menu de gauche) par groupe d'hôtes, puis par hôte puis par service dont afficher le graphique
* En parcourant l'arbre de sélection (menu de gauche) par groupe de services puis par service dont afficher le graphique

.. note::
    Les hôtes non liés à un groupe d'hôte sont ajoutés au conteneur **Hôtes orphelins**.

.. |column-chart|    image:: /images/column-chart.png
.. |zoom|	image:: /images/zoom.png
.. |save|	image:: /images/save.png
.. |text_binary_csv| image:: /images/text_binary_csv.png

