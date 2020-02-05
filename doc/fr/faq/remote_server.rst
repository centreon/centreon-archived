===============================================
Problèmes rencontrés sur Centreon Remote Server
===============================================

Liste des problèmes rencontrés :

* :ref:`Mon Remote Server et/ou ses collecteurs rattachés ne sont pas en cours d'exécution sur le serveur Centreon central<notrunning>`.
* :ref:`J'ai supprimé mon Remote Server et je ne peux plus le recréer avec l'assistant<deleteremoteserver>`.
* :ref:`Une erreur s'affiche lorsque je clique sur la page de détails d'un hôte ou d'un service<nolongerexists>`.
* :ref:`Aucune action utilisateur ne semble être prise en compte dans l'interface temps réel<noaction>`.

.. _notrunning:

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

Redémarrez le processus **cbd** sur le Remote Server : ::

    # systemctl restart cbd

Redémarrez le moteur de supervision sur chaque collecteur (y compris du Remote
Server lui-même) : ::

    # systemctl restart centengine

.. _deleteremoteserver:

J'ai supprimé mon Remote Server et je ne peux plus le recréer avec l'assistant
==============================================================================

Symptôme
--------

Vous avez configuré un Remote Server et supprimé ce dernier de la configuration.
Vous souhaitez le configurer de nouveau mais celui-ci n'apparaît plus dans la
liste de sélection des Remote Server de l'assistant de création.

Résolution
----------

Ayant déjà été configuré, il est nécessaire de remettre à zéro une information
de la base de données **centreon**. Pour cela exécutez la commande suivante : ::

    # mysql -u centreon centreon -p<password> -e "UPDATE remote_servers SET is_connected = 0 WHERE ip = '<IP Remote Server>'";

.. note::
    Remplacer **<password>** par le mot de passe associé au compte **centreon**,
    ainsi que **<IP Remote Server>** par l'adresse IP du Remote Server.

.. _nolongerexists:

Une erreur s'affiche lorsque je clique sur la page de détails d'un hôte ou d'un service
=======================================================================================

Symptôme
--------

Depuis les sous-menus du menu **Monitoring > Status Details**, lorsque je clique
sur une ressource (hôte ou service), une page blanche s'affiche avec le message
d'erreur suivant :

*The related host no longer exists in Centreon configuration. Please reload the configuration.*

Cause
-----

Une erreur est survenue durant le processus de synchronisation de la configuration
entre le serveur Centreon central et le Remote Server. Ce problème peut avoir
plusieurs origines :

L'extraction des données réalisée par le serveur Centreon central a échoué
**************************************************************************

Vous pouvez vérifier cela en exécutant la commande suivante sur le
serveur Centreon central : ::

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
données ait échoué. Suivez les étapes suivantes pour résoudre le problème
:ref:`Résolution des problèmes d'extraction des données<exportissue>`.

Si le statut est **completed**, il semble que le problème provienne du processus
d'import des données sur le Remote Server.

L'import des données réalisé par le Remote Server a échoué
**********************************************************

Vous pouvez vérifier cela en exécutant la commande suivante sur le
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
données ait échoué. Suivez les étapes suivantes pour résoudre le problème
:ref:`Résolution des problèmes d'import des données<importissue>`.

Si le statut est **completed**, la configuration a bien été importée sur votre
Remote Server.

.. _exportissue:

Résolution des problèmes d'extraction des données
*************************************************

1. Vérifiez que le processus **centcore** est en cours d'exécution sur le
   serveur Centreon central : ::

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

2. Le processus d’extraction est arrêté car le temps maximal d’exécution est atteint

Cela est visible en contrôlant que le fichier **/var/log/centreon/worker.log**
du serveur Centreon Central s’arrête à la ligne : ::

    [2018:11:08 01:54:05] Checking for pending export tasks

Ou que le fichier **/var/log/centreon/centcore.log** du serveur Centreon Central
contient : ::

    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Timeout by signal ALARM

    2018-11-08 13:54:10 - Killing child process [3926] ...
    2018-11-08 13:54:10 - Killed

Rendez-vous dans le menu **Administration > Parameters > CentCore** du serveur
Centreon Central et modifiez la variable **Timeout value for Centcore commands**
à 60s.

Redémarrez le processus **centcore** via la commande : ::

    # systemctl restart centcore

Purgez la table des tâches d’extraction::

    # mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

.. note::
    Remplacez **<password>** par le mot de passe de l'utilisateur **centreon**.

Puis régénérez la configuration du Remote Server depuis le serveur Centreon central.

3. La configuration du Remote Server est incomplète

Contrôler la configuration du Remote Server dans la base de données **centreon**
du serveur Centreon Central via la requête suivante : ::

    # mysql -u centreon -p<password> centreon -e "SELECT app_key FROM remote_servers WHERE ip = '<IP Remote Server>';"
    +---------------------------+
    | app_key                   |
    +---------------------------+
    | 0b53b30337200ccfb85ffd322 |
    +---------------------------+

.. note::
    Remplacer **<password>** par le mot de passe associé au compte **centreon**,
    ainsi que **<IP Remote Server>** par l’adresse IP du Remote Server.

Le champs **app_key** ne doit pas être vide et sa valeur doit être identique
à celle programmée sur le Remote Server. Pour contrôler cette valeur, exécutez
la requête suivante sur votre Remote Server : ::

    # mysql -u centreon -p<password> centreon -e "SELECT i.value FROM informations AS i WHERE i.key = 'appKey';"
    +---------------------------+
    | value                     |
    +---------------------------+
    | 0b53b30337200ccfb85ffd322 |
    +---------------------------+

Si tel n'est pas le cas, modifiez la valeur sur le serveur Centreon Central via
la requête : ::

    # mysql -u centreon -p<password> centreon -e "UPDATE  remote_servers SET app_key = '677479c991bbf3da744c0ff61' WHERE ip = '<IP Remote Server>';"

4. Les paramètres d'accès au Remote Server sont incomplets ou erronés

Le fichier **/var/log/centreon/worker.log** du serveur Centreon Central contient
l'erreur suivante : ::

    [2018:11:14 03:54:12] Worker cycle completed.Curl error while creating parent task: Failed connect to 10.30.2.234:80; Connection refused
    url called: 10.30.2.234/centreon/api/external.php?object=centreon_task_service&action=AddImportTaskWithParent

Vérifiez que la connexion à l'API du Remote Server est disponible et qu'aucun
pare-feu ou élément réseau ne bloque le flux en exécutant la commande suivante
depuis le serveur Centreon Central : ::

    # curl -s -d "username=admin&password=<PASSWORD>" -H "Content-Type: application/x-www-form-urlencoded" -X POST http://<IP Remote Server>/centreon/api/index.php?action=authenticate
    {"authToken":"NWJlYzM5NTkyODIzODYuMDkyNjQ0MjM="}

Vous devriez recevoir un token d'authentification ou un message indiquant une
incohérence de mot de passe (**Bad credentials**).

.. note::
    Remplacez **<PASSWORD>** par le mot de passe du compte **admin** du Remote
    Server ainsi que **<IP Remote Server>** par l'adresse IP du Remote Server.


Vérifiez que le processus Apache (httpd) est en cours d'exécution sur le Remote
Server en exécutant la commande suivante sur le Remote Server : ::

    # systemctl status httpd
    ● httpd24-httpd.service - The Apache HTTP Server
       Loaded: loaded (/usr/lib/systemd/system/httpd24-httpd.service; enabled; vendor preset: disabled)
       Active: active (running) since ven. 2019-03-22 14:29:06 CET; 2min 0s ago
     Main PID: 3735 (httpd)
       Status: "Total requests: 0; Idle/Busy workers 100/0;Requests/sec: 0; Bytes served/sec:   0 B/sec"
       CGroup: /system.slice/httpd24-httpd.service
               ├─3735 /opt/rh/httpd24/root/usr/sbin/httpd -DFOREGROUND
               ├─3736 /opt/rh/httpd24/root/usr/sbin/httpd -DFOREGROUND
               ...

si tel n'est pas le cas, redémarrez le processus **httpd** via la commande : ::

    # systemctl restart httpd24-httpd

Vérifiez que les paramètres du Remote Server sont complets et corrects en
exécutant la requête suivant sur le serveur Centreon Central : ::

    # mysql -u centreon -p<password> centreon -e "SELECT centreon_path FROM remote_servers WHERE ip = '<IP Remote Server>';"
    +---------------+
    | centreon_path |
    +---------------+
    | /centreon/    |
    +---------------+

.. note::
    Remplacer **<password>** par le mot de passe associé au compte **centreon**,
    ainsi que **<IP Remote Server>** par l’adresse IP du Remote Server.

Vérifiez que le chemin d'accès à l'interface web du Remote Server est correcte.
Sinon changez la en exécutant la requête suivante sur le serveur Centreon
Central : ::

    # mysql -u centreon -p<password> centreon -e " UPDATE remote_servers SET centreon_path = '<my value>' WHERE ip = '<IP Remote Server>';"

.. note::
    Remplacer **<my value>** par le chemin dans l'url d'accès à l'interface web
    ainsi que **<IP Remote Server>** par l’adresse IP du Remote Server.

.. _importissue:

Résolution des problèmes d'import des données
*********************************************

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

Si tel n'est pas le cas :

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

et que ces fichiers ne sont pas vides, sinon copiez les depuis le serveur Centreon
Central.

Purgez la table des tâches d'import : ::

    # mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

4. Le processus d'import est arrêté car le temps maximal d'exécution est atteint

Cela est visible en contrôlant le fichier **/var/log/centreon/worker.log** du
Remote Server s'arrête à la ligne : ::

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

    # systemctl restart centcore

Purgez la table des tâches d'import : ::

# mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

.. note::
    Remplacez **<password>** par le mot de passe de l'utilisateur **centreon**.

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

.. _noaction:

Aucune action utilisateur ne semble être prise en compte dans l'interface temps réel
====================================================================================

Symptôme
--------

Dans le menu **Monitoring > Status Details**, sélectionnez une ressource
ainsi qu'une action via la liste déroulante **More actions...** (acquittement,
re-planifier un contrôle, etc.); celle-ci ne semble pas être prise en compte.

Résolution
----------

.. note::
    Vérifiez que vous n'êtes pas dans le cas :ref:`Une erreur s'affiche
    lorsque je clique sur la page de détails d'un hôte ou d'un service
    <nolongerexists>`.

1. Vérifiez que l'échange de clé SSH a bien été réalisé entre le Remote
   Server et les collecteurs.

Puis régénérez la configuration du Remote Server depuis le serveur Centreon
central.

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

Si tel n'est pas le cas :

* Vérifier la configuration des droits d'accès à la base de données via le
  fichier **/etc/centreon/conf.pm**

* Redémarrez ce dernier via la commande : ::

    # systemctl restart centcore

3. Le processus **centengine** est-il en cours d'exécution sur les collecteurs ?

Exécutez la commande suivante sur le collecteur impacté : ::

    # systemctl status centengine
    ● centengine.service - Centreon Engine
       Loaded: loaded (/usr/lib/systemd/system/centengine.service; enabled; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 15:27:35 BST; 3 weeks 4 days ago
      Process: 20270 ExecReload=/bin/kill -HUP $MAINPID (code=exited, status=0/SUCCESS)
     Main PID: 8636 (centengine)
       CGroup: /system.slice/centengine.service
               └─8636 /usr/sbin/centengine /etc/centreon-engine/centengine.cfg

    nov. 14 10:35:00 poller-16 centreon-engine[8636]: [1542191700] [8636] SERVICE DOWNTIME ALERT: Host-5;Swap;STARTED; Service has entered a period of scheduled downtime

Si tel n'est pas le cas, redémarrez le moteur de supervision : ::

    # systemctl restart centengine

4. Le moteur de supervision ne charge pas son fichier de commande

Au redémarrage du moteur, vérifiez que le fichier **/var/log/centreon-engine/centengine.log**
contient la ligne suivante : ::

    [1542199843] [1367] Event broker module '/usr/lib64/centreon-engine/externalcmd.so' initialized successfully

Si tel n'est pas le cas, modifier la configuration du moteur sur l'interface Centreon
du serveur Centreon Central via le menu **Configuration > Pollers > Engine
configuration**. Éditez la configuration associée à votre moteur et dans
l'onglet **Data**, ajouter une directive **Event Broker** telle que : ::

    /usr/lib64/centreon-engine/externalcmd.so

Puis :ref:`déployez la configuration<deployconfiguration>`.
