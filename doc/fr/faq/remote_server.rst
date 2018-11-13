===============================================
Problèmes rencontrés sur Centreon Remote Server
===============================================

Mon Remote Server et/ou ses collecteurs rattachés ne sont pas en cours d'exécution sur le serveur Centreon central
==================================================================================================================

Symptôme
--------

Dans le menu **Configuration > Pollers**, les serveurs correspondant au Remote
Server et aux collecteurs liés à ce dernier ont la valeur **No** dans la colonne
**Is running?**

Résolution
----------

Certains processus Centreon doivent être démarrés dans un ordre précis. En
effet, le processus **cbd** du Remote Server doit être démarré avant les
processus **centengine** de chaque collecteur (y compris du Remote Server lui-
même).

Redémarrez le processus **cbd** sur le Remote Server: ::

    # systemctl restart cbd

Redémarrez le moteur de supervision sur chaque collecteur (y compris du Remote 
Server lui-même): ::

    # systemctl restart centegnine

Une erreur s'affiche lorsque je clique sur la page de détails d'un hôte ou d'un service
=======================================================================================

Symptôme
--------

Depuis les sous-menus du menu **Monitoring > Status Details**, lorsque je clique
sur une ressource (hôte ou service), une page blanche s'affiche avec le message
d'erreur suivant:

*The related host no longer exists in Centreon configuration. Please reload the configuration.*

Cause
-----

Une erreur est survenue durant le processus de synchronisation de la configuration
entre le serveur Centreon central et le Remote Server. Ce problème peut avoir
plusieurs origines:

1. L'extraction des données réalisée par le serveur Centreon central a échouée

vous pouvez vérifier cela en exécutant la commande suivante sur le
serveur Centreon central: ::

    # MariaDB [centreon]> SELECT * FROM task WHERE id = (SELECT MAX(id) FROM task WHERE params LIKE 'a:1:{s:6:"params";a:%:{s:6:"server";s:%:"<poller_id>"%');
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+
    | id  | type   | status    | parent_id | params                                                                                                                                                    | created_at          |
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+
    | 100 | export | completed |      NULL | a:1:{s:6:"params";a:4:{s:6:"server";s:2:"10";s:9:"remote_ip";s:11:"10.30.2.234";s:13:"centreon_path";s:10:"/centreon/";s:7:"pollers";a:1:{i:0;s:1:"4";}}} | 2018-11-13 15:23:42 |
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+

.. note::
    Remplacer **<poller_id>** par l'ID de votre Remote Server

Si le statut est **pending**, il semble que le processus **centcore** ne soit
pas en cours d'exécution sur votre serveur Centreon central.

Si le statut est **inprogress**, il semble que le processus d'extraction des
données est échoué. Suivez les étapes suivantes pour résoudre le problème
:ref:`Résolution des problèmes d'extraction des données<exportissue>`.

Si le statut est **completed**, il semble que le problème provienne du processus
d'import des données sur le Remote Server.

2. L'import des données réalisé par le Remote Server a échoué

Vous povez de vérifier cela en exécutant la commande suivante sur le
Remote Server : ::

    # MariaDB [centreon]> SELECT * FROM task WHERE id = (SELECT MAX(id) FROM task);
    +----+--------+-----------+-----------+--------+---------------------+
    | id | type   | status    | parent_id | params | created_at          |
    +----+--------+-----------+-----------+--------+---------------------+
    | 61 | import | completed |       100 | a:0:{} | 2018-11-13 15:23:56 |
    +----+--------+-----------+-----------+--------+---------------------+

Si le statut est **pending**, il semble que le processus **centcore** ne soit
pas en cours d'exécution sur le Remote Server.

Si le statut est **inprogress**, il semble que le processus d'extraction des
données est échoué. Suivez les étapes suivantes pour résoudre le problème
:ref:`Résolution des problèmes d'import des données<importissue>`.

Si le statut est **completed**, la configuration a bien été importée sur votre
Remote Server.

.. _exportissue:

Résolution des problèmes d'extraction des données
-------------------------------------------------

1. état processus centcore
2. timeout centcore
3. configuration du remote supprimée puis recréé
4. configuration incomplète du remote via wizzard option manuelle

.. _importissue:

Résolution des problèmes d'import des données
---------------------------------------------

1. Vérifiez que l'échange de clé SSH a bien été réalisé entre le serveur
   Centreon Central et le Remote Server

Puis régénérez la configuration du Remote Server depuis le serveur Centreon central.

2. Vérifiez que le processus **centcore** est en cours d'exécution sur les
   deux serveurs : ::

    # systemctl status centcore
    ● centcore.service - SYSV: centcore is a Centreon program that manage pollers
       Loaded: loaded (/etc/rc.d/init.d/centcore; bad; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 14:09:26 BST; 3 weeks 4 days ago
         Docs: man:systemd-sysv-generator(8)
       CGroup: /system.slice/centcore.service
               └─32385 /usr/bin/perl /usr/share/centreon/bin/centcore --logfile=/var/log/centreon/centcore.log --severity=error --config=/etc/centreon/conf.pm
    
    Warning: Journal has been rotated since unit was started. Log output is incomplete or unavailable.

Si tel n'est pas le cas,

* Vérifier la configuration des droits d'accès à la base de données via le
  fichier **/etc/centreon/conf.pm**

* Redémarrez ce dernier via la commande : ::

    # systemctl restart centcore

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

3. Fichiers manquants dans le répertoire /etc/centreon

Vérifiez que le répertoire **/etc/centreon** contient les fichiers suivants :

* instCentCore.conf
* instCentPlugins.conf
* instCentWeb.conf

et que ces fichiers ne soient pas vide, sinon les copier depuis le serveur Centreon
Central.

Purgez la table des tâches d'import : ::

# mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

4. Le processus d'import est arrêté car le temps maximal d'exécution est atteint

Cela est visible en contrôlant le fichier **/var/log/centreon/worker.log** du
Remote Server s'arrête à la ligne: ::

    [2018:11:08 01:54:05] Checking for pending export tasks: None found
    [2018:11:08 01:54:05] Checking for pending import tasks

Ou le fichier **/var/log/centreon/centcore.log** du Remote Server contient : ::

    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM
    
    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM
    
    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Timeout by signal ALARM
    
    2018-11-08 13:54:10 - Killing child process [3926] ...
    2018-11-08 13:54:10 - Killed

Rendez-vous dans le menu **Administration > Parameters > CentCore** du Remote
Server et modifiez la variable **Timeout value for Centcore commands** à 60s.

Redémarrez le processus **centcore** via la commande : ::

    # systemctl start centcore

Purgez la table des tâches d'import : ::

# mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

Aucune action utilisateur semble être prise en compte dans l'interface temps réel
=================================================================================
