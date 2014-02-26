==============================
Statistiques de l'ordonnanceur
==============================

Afin de pouvoir visualiser les performances des ordonnanceurs, il est possible de visualiser dans l'interface web les statistiques des ordonnanceurs.
Il est également possible de visualiser les statistiques de Centreon Broker.

***************************
Informations de performance
***************************

Pour visualiser les informations de performances de votre ordonnanceur :

#. Rendez-vous dans **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Choisissez votre ordonnanceur dans la liste **Collecteur**

.. image :: /images/guide_utilisateur/supervision/03statsordonnanceur.png
   :align: center 

Plusieurs tableaux permettent de visualiser les performances de vos ordonnanceurs :

* Le tableau **Actuellement contrôlés** permet de visualiser le nombre d'hôtes et de services controlés depuis la dernière minute, les dernières cinq minutes, le dernier quart d'heure ou la dernière heure
* Le tableau **Temps de latence des contrôles** permet de visualiser les temps de latence minimum, maximum et moyen des contrôles effectués sur les hôtes et les services. Plus le temps de latence est bas, plus les contrôles sont rapides
* Le tableau **Utilisation du buffer** permet de visualiser le nombre de buffer utilisé [ TODO Besoin d'infos à ce sujet]
* Le tableau **Statut** donne un bref aperçu des statuts pour les hôtes et les services
* Le tableau **Temps d'exécution des contrôles** permet de visualiser le temps d'exécution d'une sonde c'est à dire le temps entre son lancement et le moment où elle récupère l'information

**********************
Statistiques du broker
**********************

Pour visualiser les statistiques de Centreon Broker :

#. Rendez-vous dans **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Dans le menu de gauche, cliquez sur **Statistiques du broker**
#. Choisissez votre collecteur dans la liste **Collecteur**

.. image :: /images/guide_utilisateur/supervision/03statsbroker.png
   :align: center 

Les performances de Centreon Broker sont classées par fichier XML.
Chaque fichier XML correspond à une entitée de Centreon Broker (module ordonnanceur, Broker-RRD, Broker-Central).

Pour chaque fichier XML, l'interface web de Centreon affiche :

* La liste des modules de Centreon Broker chargé
* Les performances d'entrée/sortie

Les performances d'entrée/sortie
================================

Chaque performance contient plusieurs informations :

.. image :: /images/guide_utilisateur/supervision/03brokerperf.png
   :align: center 

* Le champ **Statut** contient le statut de l'entrée ou de la sortie
* Le champ **Temporary recovery mode** [TODO : Besoin d'informations]
* Le champ **accepted events** indique les évènements que le broker accepte de recevoir
* Le champ **Dernier évènement à** indique la date et l'heure du dernier évènement survenu
* Le champ **Vitesse de traitement des évènements** indique le nombre d'évènement traités à la seconde
* Le champ **Dernier essai de connexion** contient la date et l'heure du dernier essai de connexion
* Le champ **Dernière connexion réalisée** contient la date et l'heure de la dernière connexion réussie
* Le champ **Pairs** [ TODO Besoin d'infos]
* Le champ **Failover** indique le nom du failover de la sortie

**************
Les graphiques
**************

Il est également possible de visualiser les performances des moteurs de supervision sous la forme de graphiques de performances.
Pour cela :

#. Rendez-vous dans **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Dans le menu de gauche, cliquez sur **Graphiques**
#. Choisissez votre collecteur dans la liste **Collecteur**
#. Choisissez la période sur laquelle vous souhaitez visualiser les graphiques de performances

.. image :: /images/guide_utilisateur/supervision/03graphperf.png
   :align: center 