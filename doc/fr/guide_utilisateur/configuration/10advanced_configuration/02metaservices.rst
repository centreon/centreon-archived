=================
Les méta-services
=================

**********
Définition
**********

Un méta-service est un service virtuel obtenu en effectuant des calculs mathématiques à partir des métriques issues d'autres services.
Les méta-services sont gérées de la même manière qu'un service c'est à dire qu'il enverra des notifications, il génère un graphique de performance...
Exemple : En agrégeant le traffic entrant de toutes les interfaces réseaux d'un switch il est possible de créer un méta-service effectuant la somme de tous les bandes passantes entrantes sur le switch et de déclencher une alerte si un seuil est dépassé.

Les types de calcul
-------------------

Plusieurs types de calculs sont possibles sur les métriques récupérées :

* **Moyenne** : Centreon fait la moyenne des données de performances
* **Somme** : Centreon fait la somme des données de performances
* **Minimum** : Centreon récupère le minimum des données de performances
* **Maximum** : Centreon récupère le maximum des données de performances

Les types de sources de données
-------------------------------

Le type de source de données dépends du type de métrique de RRDTool :

* Le type **GAUGE** enregistre une valeur instantanée (température, humidité, CPU, ...)
* Le type **COUNTER** enregistre une valeur incrémentale par rapport au résultat précédent
* Le type **DERIVE** stockera la dérivée de la ligne allant de la dernière à la valeur courante de la source de données. Cela peut être utile pour des jauges, par exemple, de mesurer le taux de personnes entrant ou quittant une pièce.
* Le type **ABSOLUTE** est pour les compteurs qui se réinitialisent à la lecture. Il est utilisé pour les compteurs rapides qui ont tendance à déborder.

Plus d'informations sur le site de `RRDTools <http://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html>`_

*************
Configuration
*************

Pour ajouter un méta-service :

#. Rendez-vous dans **Configuration** ==> **Services**
#. Dans le menu de gauche, cliquez sur **Méta-services**
#. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

Informations générales
----------------------

* Le champ **Nom du Méta-Service** correspond au nom du méta-service affiché dans l'interface
* Le champ **Format de la chaîne de sortie (Formatage printf)** correspond au message retourné par le Méta Service. La valeur "%d" correspond à la valeur calculée par le méta-service
* Les champs **Niveau d'alerte** et **Niveau critique** correspondent respectivement au niveau WARNING et CRITICAL du méta-service
* Les champs **Type de calcul** et **Type de source de données** correspondent respectivement aux calculs et à la description de la source de données
* Le champ **Mode de sélection** permet de sélectionner les services contenant les métriques qui entreront dans le calcul du méta-service

Si l'option **Sélectionner les services manuellement** est sélectionné alors les métriques choisies seront issues de services sélectionnés manuellement.

Si l'option **Recherche SQL** est sélectionnée alors les services utilisés seront sélectionnés en complétant le champ **Expression SQL à rechercher de type LIKE**.
La métrique à utiliser sera dans ce cas à sélectionner dans la liste déroulante **Métrique**.

Etat du Meta Service
--------------------

* Le champ **Période de contrôle** définit la période temporelle durant laquelle l'ordonnanceur vérifie le statut du méta-service
* Le champ **Nombre de contrôles avant validation de l'état** définit le nombre de contrôles à effectuer avant de valider le statut du méta-service : lorsque le statut est validé, une notification est envoyée
* Le champ **Intervalle normal de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut du méta-service est OK
* Le champ **Intervalle non-régulier de contrôle** est exprimé en minutes. Il définit l'intervalle entre chaque vérification lorsque le statut du méta-service est non-OK

Notification
------------

* Le champ **Notification activée** permet d'activer les notifications
* La liste **Groupes de contacts liés** permet de définir les groupes de contacts qui seront alertés
* Le champ **Intervalle de notification** est exprimé en minutes et permet de définir l'intervalle de temps entre l'envoi de deux notifications
* Le champ **Période de notification** permet de définir la période de notification
* Le champ **Type de notification** définit les types de notifications envoyées

Informations supplémentaires
----------------------------

* La liste **Modèle de graphique** définit le modèle de graphique utilisé par ce méta-service
* Les champs **Statut** et **Commentaires** permettent d'activer/désactiver ou de commenter le méta-service

**************************************
Sélectionner manuellement des services
**************************************

Si vous avez choisit l'option **Sélectionner les services manuellement**, au sein de l'écran regroupant l'ensemble des méta-services :

1. Cliquez sur [ TODO Mettre l'icône] pour sélectionner les métriques entrant en jeu dans le calcul du méta-service. Ces métriques sont appelées indicateurs.
2. Cliquez sur **Ajouter**

[ TODO Mettre une capture d'écran]

* Le champ **Hôte** permet de sélectionner l'hôte auquel le service appartient
* Le champ **Service** permet de choisir le service (première liste) ainsi que la métrique au sein de ce service (seconde liste)
* Les champs **Statut** et **Commentaires** permettent d'activer/désactiver ou de commenter l'indicateur

3. Répétez l'opération jusqu'à avoir ajouté tous les indicateurs nécessaires au calcul du méta-service
