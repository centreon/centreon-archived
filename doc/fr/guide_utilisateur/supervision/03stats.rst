==============================
Statistiques de l'ordonnanceur
==============================

L'interface Centreon propose à l'utilisateur de visualiser les statistiques de l'ensemble des ordonnanceurs ainsi que celles liées au broker.

***************************
Informations de performance
***************************

Pour visualiser les informations de performances de votre ordonnanceur :

#. Rendez-vous dans le menu **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Dans le menu de gauche,cliquez sur **Informations de performance**
#. Choisissez votre ordonnanceur dans la liste déroulante **Collecteur**

.. image :: /images/guide_utilisateur/supervision/03statsordonnanceur.png
   :align: center 

Plusieurs tableaux permettent de visualiser les performances de vos ordonnanceurs :

* Le tableau **Actuellement contrôlés** permet de visualiser le nombre d'hôtes et de services contrôlés depuis la dernière minute, les cinq dernières minutes, le dernier quart d'heure ou la dernière heure.
* Le tableau **Temps de latence des contrôles** permet de visualiser les temps de latence minimum, maximum et moyen des contrôles effectués sur les hôtes et les services.
* Le tableau **Utilisation du buffer** permet de visualiser le nombre de commandes externes en attente de traitements par l'ordonnanceur.
* Le tableau **Statut** donne un bref aperçu des statuts pour les hôtes et les services
* Le tableau **Temps d'exécution des contrôles** permet de visualiser le temps d'exécution d'une sonde c'est à dire le temps entre son lancement et le moment où elle transmet l'information à l'ordonnanceur.

.. warning::
    Plus le temps de latence est élevée, plus les contrôles sont exécutés en retard via à vis de l'heure initiale programmée par l'ordonnanceur. Cela implique une potentielle charge élevée du serveur.

.. warning::
    Dans le cas d'une supervision passive injectant de nombreuses commandes externes à l'ordonnanceur, il est nécessaire de contrôler cette valeur. En effet, si celle-ci est trop proche de la taille limite, il est possible de perdre des commandes, il faut donc augmenter la taille du buffer.

.. warning::
    Plus le temps d'exécution est élevé, plus cela est pénalisant pour l'exécution des autres processus en file d'attente et génère de la latence. Les plugins doivent être performants pour ne pas engendrer de latence.

**********************
Statistiques du broker
**********************

Pour visualiser les statistiques de Centreon Broker :

#. Rendez-vous dans le menu **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Dans le menu de gauche, cliquez sur **Statistiques du broker**
#. Choisissez votre collecteur dans la liste **Collecteur**

.. image :: /images/guide_utilisateur/supervision/03statsbroker.png
   :align: center 

Les performances de Centreon Broker sont classées entités de Centreon Broker (module ordonnanceur, Broker-RRD, Broker-Central).

Pour chaque entité, l'interface web de Centreon affiche :

* La liste des modules de Centreon Broker chargé
* Les performances d'entrée/sortie

Les performances d'entrée/sortie
================================

Chaque performance contient plusieurs informations :

.. image :: /images/guide_utilisateur/supervision/03brokerperf.png
   :align: center 

* Le champ **Statut** contient le statut de l'entrée, de la sortie ou l'état du module lui même
* Le champ **Mode de récupération** indique si le fichier tampon du module est en cours d'utilisation
* Le champ **Dernier évènement à** indique la date et l'heure du dernier évènement survenu
* Le champ **Vitesse de traitement des évènements** indique le nombre d'évènement traités à la seconde
* Le champ **Dernier essai de connexion** contient la date et l'heure du dernier essai de connexion
* Le champ **Dernière connexion réalisée** contient la date et l'heure de la dernière connexion réussie
* Le champ **Pairs** décrit les entités connectées
* Le champ **one peer retention mode** [ TODO traduction ] indique l'activation ou non du mode
* Le champ **File d'évènements** indique le nombre d'évènements à traiter
* Le champ **Fichier en cours de lecture** indique le fichier de failover en cours de lecture
* Le champ **Emplacement de lecture (offset)** indique l'emplacement de lecture associée au fichier de fialover
* Le champ **Fichier en cours d'écriture** indique que le failover est activé en précisant le nom du fichier de failover
* Le champ **Emplacement d'écriture (offset)** indique l'emplacement de d'écriture associée au fichier de failover
* Le champ **Taille maximale du fichier** indique la taille maximale du fihier de failover
* Le champ **Failover** indique le fichier temporaire de secours associé

**************
Les graphiques
**************

Il est également possible de visualiser les performances des moteurs de supervision sous la forme de graphiques de performances.
Pour cela :

#. Rendez-vous dans le menu **Accueil** ==> **Statistiques de l'ordonnanceur**
#. Dans le menu de gauche, cliquez sur **Graphiques**
#. Choisissez votre collecteur dans la liste **Collecteur**
#. Choisissez la période sur laquelle vous souhaitez visualiser les graphiques de performances

.. image :: /images/guide_utilisateur/supervision/03graphperf.png
   :align: center 
