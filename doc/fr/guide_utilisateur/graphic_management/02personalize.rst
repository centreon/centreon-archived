============================
Personnaliser les graphiques
============================

*************************
Les modèles de graphiques
*************************

Définition
==========

Les modèles de graphiques sont des modèles qui permettent de mettre en forme les graphiques.
Les modèles de graphiques permettent de configurer plusieurs paramètres de présentation dont la mesure de l'axe des ordonnées, la largeur et la hauteur du graphique ou encore les différentes couleurs...

Configuration
=============

Pour ajouter un nouveau modèle de graphique :

#. Rendez-vous dans le menu **Vues** ==> **Graphiques**
#. Dans le menu de gauche cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/graphic_management/02addgraph_template.png
   :align: center 

Informations générales
----------------------

* Le champ **Nom du Modèle** permet de définir un nom pour le modèle de graphe
* Le champ **Label Vertical** contient la légende pour l'axe des ordonnées (type de données mesurées)
* Les champs **Largeur** et **Hauteur** sont exprimés en pixels et expriment respectivement la largeur et la hauteur du modèle
* Le champ **Limite inférieure** définit la limite minimale de l'axe des ordonnées
* Le champ **Limite supérieure** définit la limite maximale de l'axe des ordonnées
* La liste **Base** définit la base de calcul pour les données lors de la mise à l'échelle des ordonnées du graphique. Utilisez 1024 pour des mesures comme l'octet (1 Ko = 1024 octets) et 1000 pour des mesures comme le volt (1 kV = 1000 Volts). 

.. note::
    Si la case "Taille max" est cochée, le graphique sera automatiquement mis à l'échelle de la valeur maximale des ordonnées présentées sur la prériode donnée.

Légende
-------

* Le champ **Couleur d'arrière plan de la grille** définit la couleur d'arrière plan de la grille, espace de d'évolution des données.
* Le champ **Couleur de présentation de l’échelle principale** définit la grille, pour l'échelle principale.
* Le champ **Couleur de présentation de l’échelle secondaire** définit la grille, pour l'échelle secondaire. 
* Le champ **Couleur du contour du graphique** définit la couleur du contour.
* Le champ **Couleur d'arrière plan** définit la couleur d'arrière plan du graphique.
* Le champ **Couleur du texte** définit la couleur du texte au sein du graphique.
* Le champ **Couleur de la flèche des axes** définit la couleur des flèches des abscisses et des ordonnées.
* Le champ **Couleur du haut** définit la couleur de la bordure gauche et haute de l'image.
* Le champ **Couleur du bas** définit la couleur de la bordure droite et basse de l'image.
* Si la case **Séparer les courbes** est cochée, alors les courbes sont automatiquement séparées lors de l'affichage.
* Si la case **Mise à l'échelle** est cochée, alors le graphique est automatiquement mis à l'échelle par le moteur de génération du graphique.
* Si la case **Modèle graphique par défaut de Centreon** est cochée, ce modèle devient le modèle par défaut pour tous les graphiques qui n'ont aucun modèle définit.
* Le champ **Commentaires** permet de commenter le modèle de graphique.

Utiliser un modèle de graphe
============================

Vous pouvez ajouter ce modèle de présentation lors de l'édition de l'objet à :

* Un service (ou un modèle de service) en se rendant dans l'onglet **Informations supplémentaires du service**.
* Une commande.

***********
Les courbes
***********

Définition
==========

Une courbe est la représentation de l'évolution des données de performances (métriques issues de la collecte) vibile via les graphiques de performance. Un graphique peut contenir plusieurs courbes.
Il est possible de personnaliser les courbes en modifiant certains paramètres : l'allure des courbes, la position des courbes sur le graphique, la légende ainsi que les informations complémentaires (moyenne, valeur totale...).

Configuration
=============

Pour ajouter un nouveau modèle de courbes :

#. Rendez-vous dans le menu **Vues** ==> **Graphiques**
#. Dans le menu de gauche cliquez sur **Courbes**
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/graphic_management/02addcurve.png
   :align: center 

* Le champ **Nom du modèle** définit le nom du modèle.
* Le champ **Source de données Hôtes/Service** définit le service pour lequel sera utilisée cette courbe. Si ces informations ne sont pas renseignées, cette définition de courbe s'appliquera à l'ensemble des services dans lesquels cette métrique apparait.
* Le champ **Nom de la source de données** permet de sélectionner la métrique qui utilisera cette définition. La liste **Liste des métriques connues** permet de choisir les métriques déjà existantes utilisées par les services.
* Si la case **Empiler** est cochée, cette courbe s'empilera ('stacking') sur les autres (utile pour voir la proportion d'une métrique par rapport à une autre).
* Si la case **Empiler** est cochée, la liste **Ordre** permet de définir l'ordre d'affichage/empilage de la courbe (plus le nombre est petit, plus il sera proche de l'abscisse).
* Si la case **Inverser** est cochée, la courbe est inversée ( opposée de la valeur absolue) par rapport à l'axe des ordonnées (utile pour voir la proportion du trafic entrant par rapport au trafic sortant).
* La liste **Epaisseur** exprime l'épaisseur de la ligne du trait de la courbe (exprimée en pixels).
* Le champ **Couleur de la courbe** définit la couleur de la courbe.
* Le champ **Couleur de l'aire** concerne la couleur de remplissage de la courbe si l'option **Remplissage** est cochée, (voir ci-dessous). Elle contient 3 champs qui correspondent respectivement aux couleurs du statut OK, WARNING et CRITICAL.
* Le champ **Transparence** définit le niveau de transparence de la couleur du contour.
* Si la case **Remplissage** est cochée, alors toute la courbe est remplie avec la couleur de l'aire définie en fonction du statut.

Les attributs ci-dessous concernent les informations situées en dessous du graphique.

* Le champ **Légende** définit la légende de la courbe.
* Si la case **Afficher uniquement la légende** est cochée, la courbe sera masquée tandis que la légende sera visible.
* La liste **Ligne vide après cette légende** permet de définir un certain nombre de lignes vides après la légende.
* Si la case **Afficher la valeur maximale** est cochée, alors la valeur maximale atteinte par la courbe sera affichée.
* Si la case **Afficher la valeur minimale** est cochée, alors la valeur minimale atteinte par la courbe sera affichée.
* Si la case **Arrondir les valeurs minimales et maximales** est cochée, alors les valeurs minimales et maximales seront arrondies.
* Si la case **Afficher la moyenne** est cochée, alors la moyenne des points de la courbe sera affichée.
* Si la case **Afficher la dernière valeur** est cochée, alors la dernière valeur collectée de la courbe sera affichée.
* Si la case **Afficher la valeur totale** est cochée, s'affiche alors la valeur totale (somme de toutes les valeurs de la courbe sur la prériode sélectionnée). 
* Le champ **Commentaires** permet de commenter la courbe.

Quelques exemples de courbes
============================

Les courbes empilées :

.. image :: /images/guide_utilisateur/graphic_management/02graphempile.png
   :align: center 

Les courbes inversées :

.. image :: /images/guide_utilisateur/graphic_management/02graphinverse.png
   :align: center 

Les courbes avec remplissage :

.. image :: /images/guide_utilisateur/graphic_management/02graphremplissage.png
   :align: center 

************************
Les métriques virtuelles
************************

Définition
==========

Les métriques virtuelles sont l'affichage de courbes résultant du traitement / aggrégation de données issues d'un jeu de données.
Le jeu de données correspond aux différentes valeurs des courbes sur la période de présentation du graphique.
La création de métriques virtuelles repose sur le langage RPN (Reverse Polish Notation).

Deux types de jeu de données sont disponibles :

* CDEF : Cette commande crée un nouvel ensemble de points à partir d'une ou plusieurs séries de données. L'aggrégation est réalisée sur chaque point (données). 
* VDEF : Le résultat de chaque aggrégation est une valeur et une composante temporelle. Ce résultant peut également être utilisé dans les divers éléments de graphique et d'impression. 

CDEF vs VDEF
------------

Le type CDEF travaille sur un ensemble de points (tableau de données). Le résultat du traitement (exemple : multiplication par 8 pour convertir des bits en octets) sera un ensemble de point.
Le type VDEF permet d'extraire le maximum d'un ensemble de point.

.. note::
    Pour plus d'informations sur la notation de type RPN, référencez-vous à la `documentation officielle RRD <http://oss.oetiker.ch/rrdtool/tut/rpntutorial.en.html>`_

Configuration
=============

Pour ajouter une métrique virtuelle :

#. Rendez-vous dans le menu **Vues** ==> **Graphiques**
#. Dans le menu de gauche, cliquez sur **Métriques** (en dessous de **Virtuals**)
#. Cliquez sur **Ajouter**

.. image :: /images/guide_utilisateur/graphic_management/02addvmetric.png
   :align: center 

* Le champ **Nom de la Métrique** définit le nom de la métrique.
* La liste **Source de données Hôte / Service** permet de définir le service depuis lequel exploiter les métriques.
* Le champ **Type DEF** définit le type de jeu de données utilisé pour calculer la courbe virtuelle.
* Le champ **Fonction RPN (Notation Polonaise Inversée)** définit la formule permettant de calculer la métrique virtuelle. 

.. note::
    Il n'est pas possible d'ajouter ensemble les métriques de différents services. Cependant, il est possible d'ajouter des métriques virtuelles pour le calcul d'une nouvelle métrique.

* Le champ **Unité de la Métrique** définit l'unité de la métrique.
* Le champ **Niveau d'alerte** définit le seuil d'alerte à afficher dans le graphique.
* Le champ **Niveau critique** définit le seuil critique à afficher dans le graphique.
* Si la case **Masquer le graphique et la légende** est cochée, alors la courbe et la légende sont cachées.
* Le champ **Commentaires** permet de commenter la métrique.
