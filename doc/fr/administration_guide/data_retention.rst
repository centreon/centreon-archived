=====================
Rétention des données
=====================

En accédant au menu **Administration > Paramètres > Options**, il est possible
de définir les durées de rétention des données de la plate-forme Centreon :

.. image:: /images/guide_exploitation/data_retention.png
    :align: center

***********************************
Stockage des données de performance
***********************************

Ce paramétrage concerne les dossiers de stockage des données de performances.
Ces dernières permettent de visualiser les graphiques de performance des métriques
collectées par la supervision, de suivre l'évolution du statut des services, ou
encore de suivre certains indicateurs des moteurs de collectes.

.. warning::
    Ces valeurs ont été paramétrées durant le processus d'installation, il
    n'est pas recommandé de changer celles-ci.

* **Chemin d'accès vers les fichiers RRDTool pour les métriques** : par défaut **/var/lib/centreon/metrics/**.
* **Chemin d'accès vers les fichiers RRDTool pour les statuts** : par défaut **/var/lib/centreon/status/**.
* **Chemin vers la base de données RRDTool pour les statistiques du moteur de supervision**: par défaut **/var/lib/centreon/nagios-perf/**.

******************************
Durée de rétention des données
******************************

Le paramétrage de la durée de rétention permet de limiter la taille de la base de données :

* **Retention duration for reporting data (Dashboard)** : durée de rétention des données des rapports de disponibilité, par défaut **365 jours**.
* **Retention duration for logs** : durée de rétention du journal d'activité des moteurs de collecte, par défaut **31 jours**.
* **Retention duration for performance data in MySQL database** : durée de rétention des données de performance en base de données, par défaut **365 jours**
* **Retention duration for performance data in RRDTool databases** : durée de rétention des données de performance pour les graphiques de performance, par défaut **180 jours**.
* **Retention duration for downtimes** : durée de rétention des données des temps d'arrêts, par défaut illimitée (0 jour).
* **Retention duration for comments** : durée de rétention des commentaires, par défaut illimitée (0 jour).
* **Retention duration for audit logs** : durée de rétention des logs d'audit, par défaut illimitée (0 jour).

.. note::
    Il est possible de ne pas sauvegarder les données de performance en base
    de données MySQL si vous n'utilisez pas d'extraction vers des logiciels
    complémentaires tels que Centreon MBI.

.. note::
    Si vous changez la durée de rétention pour les graphiques de performance,
    cette valeur ne sera utilisée que pour les nouveaux services ajoutés.
