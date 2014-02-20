===========
Les graphes
===========

**********
Définition
**********

Centreon permet de générer des graphiques à partir des informations de supervision. Il existe deux types de graphiques :

* Les graphiques de performances permettent de visualiser l'évolution des services de manière intuitive. Exemples : niveau de remplissage d'un disque dur, traffic réseau...
* Les graphiques d'historique (ou graphique des statuts) permettent de visualiser l'évolution des statuts d'un service.

Les graphiques de performances ont toujours comme abscisse une période de temps et comme ordonnée une mesure (Volts, Octets...).
Les graphiques d'historique ont toujours comme abscisse une période de temps, leurs ordonnées ne varie pas. Uniquement la couleur du graphique permet de visualiser le statut de l'objet :

* Vert pour le statut OK
* Jaune pour le statut WARNING
* Rouge pour le statut CRITICAL
* Gris pour le statut UNKNOWN

Exemple de graphique de performances :

[ TODO Mettre une image ]

Exemple de graphique d'historique :

[ TODO Mettre une image ]

*************
Visualisation
*************

Les graphiques de performances
==============================

Il y a plusieurs manières de visualiser les graphiques de performances :

* Visualiser le graphique dans la liste des services (**Supervision** ==> **Services**) en survolant l'icône [ TODO Mettre l'image]
* Visualiser le graphique dans le détail d'un service
* Se rendre dans **Vues** ==> **Graphiques** pour visualiser plusieurs graphiques

Les graphiques d'historique
===========================

Comme vu dans le chapitre **Supervision en temps-réel** il est possible de visualiser les graphiques d'historiques en visualisation les détails d'un service.

Comme vu ci-dessous en cochant la case **Affichage de l'état**, il est possible de visualiser les graphiques d'historique liés aux graphiques de performances affichés.

Visualiser plusieurs graphiques
===============================

Pour visualiser l'ensemble des graphiques, rendez-vous dans **Vues** ==> **Graphiques**.

[ TODO Mettre l'image : images/01.png]

Le menu de gauche permet de sélectionner les hôtes et/ou les services pour lesquels on souhaite visualiser les graphiques.

La barre de recherche grise appelée **Période de visualisation** permet de sélectionner la période de temps pour laquelle on souhaite visualiser les graphiques.
La liste déroulante permet de sélectionner des périodes de temps génériques. Si la liste déroulante est vide alors il est possible de choisir manuellement la période de temps en utilisant les champs **Du** et **Au**.

Plusieurs actions sont possibles sur les graphiques :

* **Séparer les courbes** : sépare plusieurs courbes d'un graphique en plusieurs graphiques contenant chacun une courbe
* **Affichage de l'état** : affiche les graphiques d'historique liés aux graphiques de performances affichés

Pour exploiter les données des graphiques, il est possible de :

* Visualiser le graphique de performance sur un jour, une semaine, un mois et une année en cliquant sur le graphique de performances de votre choix
* De zoomer sur le graphique en cliquant sur l'icône [ TODO Mettre l'icône]
* De sauvegarder le graphique en cliquant sur l'icône [ TODO Mettre l'icône]
* De télécharger l'ensemble des données qui composent le graphique au format .csv en cliquant sur l'icône [ TODO Mettre l'icône]