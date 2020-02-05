=========================================
Administration de la plate-forme Centreon
=========================================

Comment la fonction **Supprimer des graphiques** fonctionne ?
=============================================================

Afin de préserver les performances globales, cette action ne supprime pas toutes
les données de la base de données juste après son lancement. Les entrées seront
retirées des tables **index_data** et **metrics** mais pas de la table **data_bin**.

La principale raison est que la table **data_bin** stocke rapidement une énorme quantité
de données et utilise le moteur MyISAM qui ne prend pas en charge le verrouillage par 
ligne Si vous essayez de supprimer trop d'entrées simultanément, vous pourriez bloquer 
toute votre base de données pendant plusieurs heures.

Quoi qu'il en soit, cela ne signifie pas que les données resteront dans votre base de données
indéfiniment. Elles seront supprimées plus tard, en fonction de votre politique de rétention
des données programmée.

Mon dashboard sur plusieurs jours est indetermminé, que dois-je contrôler?
==========================================================================

Il s'agit d'un bug du à certaines versions mysql avec les tables partitionées
(https://bugs.mysql.com/bug.php?id=70588).
Remplacer les '=' par des LIKE dans les requêtes corrige le problème mais réduit les performances.

Nous vous recommandons de mettre à jour votre SGBD
MySQL 5.5.36, 5.6.16, 5.7.4,
MariaDB  10.0.33, 10.1.29, 10.2.10.

Aucun graphique ne semble être généré, que dois-je contrôler?
=============================================================

Il ya plusieurs choses à vérifier lorsque les RRDs ne semblent pas être générés.

Espace disque
-------------

Par défaut, les fichiers contenant les graphiques (.rrd) sont stockés dans le
répertoire **/var/lib/centreon/metrics**. Il est évidemment nécessaire de disposer 
d'assez d'espace sur votre système de fichiers.

Permissions
-----------

Est-ce que les fichiers contenant les graphiques (.rrd) peuvent être écrit dans le 
répertoire **/var/lib/centreon/metrics** ?
Le processus qui écrit dans ce répertoire est soit **cdb** soit **centstorage**.

Plugins
-------

Est-ce que vos plugins génèrent correctement les données de performance ?
Se référer à la :ref:`documentation officielle <centreon-engine:centengine_plugin_api>` 
pour plus d'informations. 

Centreon Broker
---------------

Centreon Broker doit être correctement configuré. Se référer à la 
:ref:`documentation de configuration <advance_conf_broker>` pour plus d'informations.

Le démon cbd rrd doit être en cours d'exécution :

::

    # systemctl status cbd
    ● cbd.service - Centreon Broker watchdog
       Loaded: loaded (/etc/systemd/system/cbd.service; enabled; vendor preset: disabled)
       Active: active (running) since mer. 2018-07-18 17:46:03 CEST; 2 months 9 days ago
      Process: 21410 ExecReload=/bin/kill -HUP $MAINPID (code=exited, status=0/SUCCESS)
     Main PID: 9537 (cbwd)
       CGroup: /system.slice/cbd.service
               ├─9537 /usr/sbin/cbwd /etc/centreon-broker/watchdog.json
               ├─9539 /usr/sbin/cbd /etc/centreon-broker/central-rrd.json
               └─9540 /usr/sbin/cbd /etc/centreon-broker/central-broker.json
