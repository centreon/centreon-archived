=========================
Personnaliser les graphes
=========================

**********************
Les modèles de graphes
**********************

Définition
==========

Les modèles de graphes sont des modèles qui permettent de mettre en forme les graphes.
Les modèles de graphes permettent de configurer plusieurs paramètres du graphe dont la mesure de l'axe des ordonnées, la largeur et la hauteur du graphe, les différentes couleurs...

Configuration
=============

Pour ajouter un nouveau modèle de graphe :

#. Rendez-vous dans **Vues** ==> **Graphiques**
#. Dans le menu de gauche cliquez sur **Modèles**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Informations générales
----------------------

* Le champ **Nom du Modèle** permet de définir un nom pour le modèle de graphe
* Le champ **Label Vertical** contient la légende pour l'axe des ordonnées (type de données mesurées)
* Les champs **Largeur** et **Hauteur** sont exprimés en pixels et expriment respectivement la largeur et la hauteur du modèle
* Le champ **Limite inférieure** définit la limite minimale de l'axe des ordonnées
* Le champ **Limite supérieure** définit la limite maximale de l'axe des ordonnées
* La liste **Base** définit la base de calcul pour les données : Utiliser 1024 pour des mesures comme l'octet (1 Ko = 1024 octets) et 1000 pour des mesures comme le volt (1 kV = 1000 Volts)

Légende
-------

* Le champ **Couleur d'arrière plan de la grille** définit la couleur d'arrière plan de la grille
* Le champ **Couleur principale de la grille** définit la couleur principale de la grille
* Le champ **Couleur secondaire de la grille** définit la couleur secondaire de la grille
* Le champ **Schéma de couleurs** [ TODO N'est-il pas nécessaire de changer la légende ?] définit la couleur du contour
* Le champ **Couleur d'arrière plan** définit la couleur d'arrière plan du graphe
* Le champ **Couleur du texte** définit la couleur du texte au sein du graphe
* Le champ **Couleur de la flèche des axes** définit la couleur des flèches des abscisses et des ordonnées
* Le champ **Couleur du haut** définit la couleur de la bordure gauche et haute de l'image
* Le champ **Couleur du bas** définit la couleur de la bordure droite et basse de l'image
* Si la case **Séparer les courbes** est cochée alors les courbes sont automatiquement séparées
* Si la case **Mise à l'échelle** est cochée alors le graphe est automatiquement mis à l'échelle
* Si la case **Modèle graphique par défaut de Centreon** est cochée, ce modèle devient le modèle par défaut pour tous les graphes qui n'ont aucun modèle définit
* Le champ **Commentaires** permet de commenter le modèle de graphe

Utiliser un modèle de graphe
============================

Comme vu dans les chapitres précédent, il est possible d'ajouter un modèle de graphe à :

* Un service (ou un modèle de services) en se rendant dans l'onglet **Informations supplémentaires du service**
* Une commande

***********
Les courbes
***********

Définition
==========

Les courbes sont visibles sur les graphiques. Un graphique peut contenir plusieurs courbes.
Il est possible de personnaliser les courbes en modifiant certains paramètres : l'allure des courbes, la position des courbes sur le graphique, la légende ainsi que les informations complémentaires (moyenne, valeur totale...).

Configuration
=============

Pour ajouter un nouveau modèle de courbes :

#. Rendez-vous dans **Vues** ==> **Graphiques**
#. Dans le menu de gauche cliquez sur **Courbes**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

* Le champ **Nom du modèle** définit le nom du modèle
* Le champ **Source de données Hôtes/Service** définit le service pour lequel sera utilisé cette courbe (si ces informations ne sont pas renseignées, cette courbe peut s'appliquer à l'ensemble des services)
* Le champ **Nom de la source de données** permet de sélectionner la métrique qui utilisera cette courbe (La liste **Liste des métriques connues** permet de choisir les métriques déjà existantes utilisées par les services)
* Si la case **Empiler** est cochée, cette courbe s'empilera sur les autres (utile pour voir la proportion d'une métrique par rapport à une autre)
* Si la case **Empiler** est cochée, la liste **Ordre** permet de définir l'ordre d'affichage de la courbe (plus le nombre est petit, plus il sera proche de l'abscisse)
* Si la case **Inverser** est cochée, la courbe est inversée sur l'axe des ordonnées (utile pour voir la proportion du trafic entrant par rapport au trafic sortant)
* La liste **Epaisseur** exprime l'épaisseur de la ligne de la courbe (exprimée en pixels)
* Le champ **Couleur de la courbe** définit la couleur de la courbe
* Le champ **Couleur de l'aire** concerne la couleur de remplissage de la courbe si l'option **Remplissage** est cochée (voir ci-dessous). Elle contient 3 champs qui correspondent respectivement aux couleurs du statut OK, WARNING et CRITICAL.
* Le champ **Transparence** définit le niveau de transparence de la couleur du contour
* Si la case **Remplissage** est cochée alors toute la courbe est remplie avec la couleur du contour

Les attributs ci-dessous concernent les informations situées en dessous du graphique.

* Le champ **Légende** définit la légende de la courbe
* Si la case **Afficher uniquement la légende** est cochée, la courbe sera masquée
* La liste **Ligne vide après cette légende** permet de définir un certain nombre de lignes vides après la légende
* Si la case **Afficher la valeur maximale** est cochée, alors la valeur maximale atteinte par la courbe sera affichée
* Si la case **Afficher la valeur minimale** est cochée, alors la valeur minimale atteinte par la courbe sera affichée
* Si la case **Arrondir les valeurs minimales et maximales** est cochée alors les valeurs minimales et maximales seront arrondis
* Si la case **Afficher la moyenne** est cochée alors la moyenne des points de la courbe sera affichée
* Si la case **Afficher la dernière valeur** est cochée, alors la dernière valeur de la courbe sera affichée
* Si la case **Afficher la valeur totale** est cochée, [ TODO je ne trouve pas à quoi correspond cette option ?]
* Le champ **Commentaires** permet de commenter la courbe

Quelques exemples de courbes
============================

Les courbes empilées :

[ TODO Mettre une image]

Les courbes inversées :

[ TODO Mettre une image]

Les courbes avec remplissage :

[ TODO Mettre une image]

************************
Les métriques virtuelles
************************

Définition
==========

Les métriques virtuelles sont des métriques crées à partir d'autres métriques.
Afin de pouvoir créer ces métriques virtuelles, nous utilisons le langage RPN (Reverse Polish Notation).

Afin de pouvoir calculer les métriques virtuelles, deux types de langages peuvent être utilisées :

* CDEF
* VDEF

Pour plus d'informations sur la notation de type RPN, rendez-vous dans `Documentation RRD <http://oss.oetiker.ch/rrdtool/tut/rpntutorial.en.html>`_

Configuration
=============

Pour ajouter une métrique virtuelle :

#. Rendez-vous dans **Vues** ==> **Graphiques**
#. Dans le menu de gauche, cliquez sur **Métriques** (en dessous de **Virtuals**)
#. Cliquez sur **Ajouter**

[ TODO Mettre une image]

* Le champ **Nom de la Métrique** définit le nom de la métrique
* La liste **Source de données Hôte / Service** permet de définir le service depuis lequel nous allons exploiter les métriques
* Le champ **Type DEF** définit le langage utilisé pour calculer la courbe virtuelle
* Le champ **Fonction RPN (Notation Polonaise Inversée)** définit la formule permettant de calculer la métrique virtuelle. 

Notez bien : Il n'est pas possible d'ajouter ensemble les métriques de différents services.
Il est possible d'ajouter des métriques virtuelles pour le calcul de la nouvelle métrique

* Le champ **Unité de la Métrique** définit l'unité de la métrique
* Le champ **Niveau d'alerte** définit le seuil d'alerte à afficher dans le graphique
* Le champ **Niveau critique** définit le seuil critique à afficher dans le graphique
* Si la case **Masquer le graphique et la légende** est cochée alors la courbe et la légende sont cachés
* Le champ **Commentaires** permet de commenter la métrique