======================================
Known issues on Centreon Remote Server
======================================

List of known issues:

* :ref:`My Remote Server and / or attached Pollers are not running on the central Centreon server<notrunning>`.
* :ref:`I deleted my Remote Server and cannot recreate it with the wizard<deleteremoteserver>`.
* :ref:`An error is displayed when I click on the details page of a host or a service<nolongerexists>`.
* :ref:`Actions in the real-time user interface are not taken into account<noaction>`.

.. _notrunning:

My Remote Server and / or attached Pollers are not running on the central Centreon server
=========================================================================================

Symptom
-------

In the **Configuration > Pollers** menu, the servers corresponding to the Remote
Server and the attached pollers are set to **No** in the **Is running?** column.

Resolution
----------

Some Centreon processes must be started in a specific order. Indeed, the **cbd**
process of the Remote Server must be started before the **centengine**
processes of each poller (including the Remote Server itself).

Restart the **cbd** process on the Remote Server: ::

    # systemctl restart cbd

Restart the scheduler on each poller (including the Remote Server itself): ::

    # systemctl restart centengine

.. _deleteremoteserver:

I deleted my Remote Server and cannot recreate it with the wizard
=================================================================

Symptom
--------

You configured a Remote Server and you deleted it from configuration. You want
to recreate it but it doesn't appear in the list of selection of Remote Server
in the wizard.

Resolution
----------

Because it has already been configured, you need to reset an information into
**centreon** database. Please execute the following SQL query: ::

    # mysql -u centreon centreon -p<password> -e "UPDATE remote_servers SET is_connected = 0 WHERE ip = '<IP Remote Server>'";

.. note::
    Replace **<password>** by the password of **centreon** user, and
    **<IP Remote Server>** by the IP address of the Remote Server.

.. _nolongerexists:

An error is displayed when I click on the details page of a host or a service
=============================================================================

Symptom
--------

From submenus of **Monitoring > Status Details** menu, when I click on a resource
(host or service), a blank page appears with the following error message:

*The related host no longer exists in Centreon configuration. Please reload the configuration.*

Cause
-----

An error occurred during the configuration synchronization process between the
central Centreon server and the Remote Server. This problem can have several
origins:

The data extraction performed by the central Centreon server failed
*******************************************************************

You can check this by running the following command on the central Centreon
server: ::

    # MariaDB [centreon]> SELECT * FROM task WHERE id = (SELECT MAX(id) FROM task WHERE params LIKE 'a:1:{s:6:"params";a:%:{s:6:"server";s:%:"<poller_id>"%');
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+
    | id  | type   | status    | parent_id | params                                                                                                                                                    | created_at          |
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+
    | 100 | export | completed |      NULL | a:1:{s:6:"params";a:4:{s:6:"server";s:2:"10";s:9:"remote_ip";s:11:"10.30.2.234";s:13:"centreon_path";s:10:"/centreon/";s:7:"pollers";a:1:{i:0;s:1:"4";}}} | 2018-11-13 15:23:42 |
    +-----+--------+-----------+-----------+-----------------------------------------------------------------------------------------------------------------------------------------------------------+---------------------+

.. note::
    Replace **<poller_id>** by the ID of your Remote Server.

If the status is **pending**, the **centcore** process
apparently is not running on your central Centreon server.

If the status is **inprogress**, the extraction process apparently
failed. Follow these steps to fix the problem
:ref:`Solving data extraction problems<exportissue>`.

If the status is **completed**, the problem apparently comes from
data import process on the Remote Server.

Data importation failed on the Remote Server
********************************************

You can check this by running the following command on the
Remote Server: ::

    # MariaDB [centreon]> SELECT * FROM task WHERE id = (SELECT MAX(id) FROM task);
    +----+--------+-----------+-----------+--------+---------------------+
    | id | type   | status    | parent_id | params | created_at          |
    +----+--------+-----------+-----------+--------+---------------------+
    | 61 | import | completed |       100 | a:0:{} | 2018-11-13 15:23:56 |
    +----+--------+-----------+-----------+--------+---------------------+

If the status is **pending**, the **centcore** process
apparently is not running on the Remote Server.

If the status is **inprogress**, the import process apparently
failed. Follow these steps to fix the problem
:ref:`Solving data import process problems<importissue>`.

If the status is **completed**, the configuration was successfully imported
on the Remote Server.

.. _exportissue:

Solving data extraction problems
********************************

1. Check that the **centcore** process is running on the
   central Centreon server: ::

    # systemctl status centcore
    ● centcore.service - SYSV: centcore is a Centreon program that manage pollers
       Loaded: loaded (/etc/rc.d/init.d/centcore; bad; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 14:09:26 BST; 3 weeks 4 days ago
         Docs: man:systemd-sysv-generator(8)
       CGroup: /system.slice/centcore.service
               └─32385 /usr/bin/perl /usr/share/centreon/bin/centcore --logfile=/var/log/centreon/centcore.log --severity=error --config=/etc/centreon/conf.pm

    Warning: Journal has been rotated since unit was started. Log output is incomplete or unavailable.

If it is not running,

* Check the database access rights configuration in the
  file **/etc/centreon/conf.pm**

* Restart the centcore process: ::

    # systemctl restart centcore

Then generate the Remote Server configuration from the central Centreon server.

2. Extraction process execution timeout

The process stopped because of an execution timeout if the **/var/log/centreon/worker.log** file
last line is: ::

    [2018:11:08 01:54:05] Checking for pending export tasks

Or if the **/var/log/centreon/centcore.log** file on the central Centreon server
includes: ::

    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Timeout by signal ALARM

    2018-11-08 13:54:10 - Killing child process [3926] ...
    2018-11-08 13:54:10 - Killed

Go to **Administration > Parameters > CentCore** menu on the central
Centreon server and set the **Timeout value for Centcore commands**
variable to 60s.

Restart the centcore process: ::

    # systemctl restart centcore

Purge the extraction task table in the database::

    # mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

.. note::
    Replace **<password>** with the **centreon** user password.

Then generate the Remote Server configuration from the central Centreon server.

3. Incomplete Remote Server configuration

Verify the Remote Server configuration in the **centreon** database
on the Central Centreon server as follows: ::

    # mysql -u centreon -p<password> centreon -e "SELECT app_key FROM remote_servers WHERE ip = '<IP Remote Server>';"
    +---------------------------+
    | app_key                   |
    +---------------------------+
    | 0b53b30337200ccfb85ffd322 |
    +---------------------------+

.. note::
    Replace **<password>** with the **centreon** user password,
    and **<IP Remote Server>** with the Remote Server IT address.

The **app_key** field should not be empty and its value should be identical
on the Remote Server. To get this value, execute this query
on the Remote Server: ::

    # mysql -u centreon -p<password> centreon -e "SELECT i.value FROM informations AS i WHERE i.key = 'appKey';"
    +---------------------------+
    | value                     |
    +---------------------------+
    | 0b53b30337200ccfb85ffd322 |
    +---------------------------+

If the two values don't match, update the field on the Central Server
as follows: ::

    # mysql -u centreon -p<password> centreon -e "UPDATE  remote_servers SET app_key = '677479c991bbf3da744c0ff61' WHERE ip = '<IP Remote Server>';"

4. Access parameters on the Remote Server are incomplete or invalid

The **/var/log/centreon/worker.log** file on the Central Server includes
this error line: ::

    [2018:11:14 03:54:12] Worker cycle completed.Curl error while creating parent task: Failed connect to 10.30.2.234:80; Connection refused
    url called: 10.30.2.234/centreon/api/external.php?object=centreon_task_service&action=AddImportTaskWithParent

Check the connection to the Remote Server API and verify
no firewall or other network element forbids the communication. On the Central Server
execute the following command: ::

    # curl -s -d "username=admin&password=<PASSWORD>" -H "Content-Type: application/x-www-form-urlencoded" -X POST http://<IP Remote Server>/centreon/api/index.php?action=authenticate
    {"authToken":"NWJlYzM5NTkyODIzODYuMDkyNjQ0MjM="}

You should receive an authentication token or a **Bad credentials** message.

.. note::
    Replace **<PASSWORD>** with the Remote Server **admin** account password
    and **<IP Remote Server>** with the Remote Server IP address.

Check the Apache process (httpd) is up and running on the Remote Server
by running this command on the Remote Server: ::

    # systemctl status httpd24-httpd
    ● httpd24-httpd.service - The Apache HTTP Server
       Loaded: loaded (/usr/lib/systemd/system/httpd24-httpd.service; enabled; vendor preset: disabled)
       Active: active (running) since ven. 2019-03-22 14:29:06 CET; 2min 0s ago
     Main PID: 3735 (httpd)
       Status: "Total requests: 0; Idle/Busy workers 100/0;Requests/sec: 0; Bytes served/sec:   0 B/sec"
       CGroup: /system.slice/httpd24-httpd.service
               ├─3735 /opt/rh/httpd24/root/usr/sbin/httpd -DFOREGROUND
               ├─3736 /opt/rh/httpd24/root/usr/sbin/httpd -DFOREGROUND
               ...

If it is not running, restart the **httpd** process: ::

    # systemctl restart httpd24-httpd

Check that the Remote Server parameters are complete and valid
with this command on the Central Server: ::

    # mysql -u centreon -p<password> centreon -e "SELECT centreon_path FROM remote_servers WHERE ip = '<IP Remote Server>';"
    +---------------+
    | centreon_path |
    +---------------+
    | /centreon/    |
    +---------------+

.. note::
    Replace **<password>** with the **centreon** account password,
    and **<IP Remote Server>** with the Remote Server IP address.

Check the Remote Server web interface path is correct.
If it isn't, change the path on the Centreon Server with this query: ::

    # mysql -u centreon -p<password> centreon -e " UPDATE remote_servers SET centreon_path = '<my value>' WHERE ip = '<IP Remote Server>';"

.. note::
    Replace **<my value>** with the web interface url
    and **<IP Remote Server>** with the Remote Server IP address.

.. _importissue:

Solving data importation problems
*********************************

1. Verify the SSH key exchange was performed between the Central and Remote Servers

Then generate the Remote Server configuration from the central Centreon server.

2. Check the **centcore** process is up and running on both servers: ::

    # systemctl status centcore
    ● centcore.service - SYSV: centcore is a Centreon program that manage pollers
       Loaded: loaded (/etc/rc.d/init.d/centcore; bad; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 14:09:26 BST; 3 weeks 4 days ago
         Docs: man:systemd-sysv-generator(8)
       CGroup: /system.slice/centcore.service
               └─32385 /usr/bin/perl /usr/share/centreon/bin/centcore --logfile=/var/log/centreon/centcore.log --severity=error --config=/etc/centreon/conf.pm

    Warning: Journal has been rotated since unit was started. Log output is incomplete or unavailable.

If it is not running:

* Check the database access rights configuration in the
  file **/etc/centreon/conf.pm**

* Restart the centcore process: ::

    # systemctl restart centcore

Then generate the Remote Server configuration from the central Centreon server.

3. Missing files in /etc/centreon

Check that **/etc/centreon** includes the following files and that they are not empty:

* instCentCore.conf
* instCentPlugins.conf
* instCentWeb.conf

If missing or empty, copy them from the Central Server.

Purge the import tasks table: ::

# mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

Then generate the Remote Server configuration from the central Centreon server.

4. The process stopped because of an execution timeout if the **/var/log/centreon/worker.log** file
last line is: ::

    [2018:11:08 01:54:05] Checking for pending export tasks

Or if the **/var/log/centreon/centcore.log** file on the central Centreon server
includes: ::

    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Receiving die: Timeout by signal ALARM

    2018-11-08 13:54:10 - Dont die...
    2018-11-08 13:54:10 - Timeout by signal ALARM

    2018-11-08 13:54:10 - Killing child process [3926] ...
    2018-11-08 13:54:10 - Killed

Go to **Administration > Parameters > CentCore** menu on the central
Centreon server and set the **Timeout value for Centcore commands**
variable to 60s.

Restart the centcore process: ::

    # systemctl restart centcore

Purge the extraction task table in the database::

    # mysql -u centreon -p<password> centreon -e "DELETE FROM task WHERE status NOT IN ("completed");"

.. note::
    Replace **<password>** with the **centreon** user password.

Then generate the Remote Server configuration from the central Centreon server.

.. _noaction:

Actions in the real-time user interface are not taken into account
==================================================================

Symptom
--------

In the **Monitoring > Status Details** menu, select a resource,
then select an action in the **More actions...** drop-down list (acknowlege,
recheck, etc.); This action apparently is not taken into account.

Resolution
----------

.. note::
    Check this symptom first :ref:`An error is displayed when I click on the details page of a host or a service<nolongerexists>`.

1. Verify the SSH key exchange was performed between the Central and Remote Servers

Then generate the Remote Server configuration from the central Centreon server.

2. Check the **centcore** process is up and running on both servers: ::

    # systemctl status centcore
    ● centcore.service - SYSV: centcore is a Centreon program that manage pollers
       Loaded: loaded (/etc/rc.d/init.d/centcore; bad; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 14:09:26 BST; 3 weeks 4 days ago
         Docs: man:systemd-sysv-generator(8)
       CGroup: /system.slice/centcore.service
               └─32385 /usr/bin/perl /usr/share/centreon/bin/centcore --logfile=/var/log/centreon/centcore.log --severity=error --config=/etc/centreon/conf.pm

    Warning: Journal has been rotated since unit was started. Log output is incomplete or unavailable.

If it is not running:

* Check the database access rights configuration in the
  file **/etc/centreon/conf.pm**

* Restart the centcore process: ::

    # systemctl restart centcore

Then generate the Remote Server configuration from the central Centreon server.

3. Is the **centengine** process up and running on the Pollers?

Run this command on the Poller where actions are not taken into account: ::

    # systemctl status centengine
    ● centengine.service - Centreon Engine
       Loaded: loaded (/usr/lib/systemd/system/centengine.service; enabled; vendor preset: disabled)
       Active: active (running) since ven. 2018-10-19 15:27:35 BST; 3 weeks 4 days ago
      Process: 20270 ExecReload=/bin/kill -HUP $MAINPID (code=exited, status=0/SUCCESS)
     Main PID: 8636 (centengine)
       CGroup: /system.slice/centengine.service
               └─8636 /usr/sbin/centengine /etc/centreon-engine/centengine.cfg

    nov. 14 10:35:00 poller-16 centreon-engine[8636]: [1542191700] [8636] SERVICE DOWNTIME ALERT: Host-5;Swap;STARTED; Service has entered a period of scheduled downtime

If centengine is not running, restart it: ::

    # systemctl restart centengine

4. Monitoring Engine does not load its command file

When restarting centengine, check the **/var/log/centreon-engine/centengine.log**
file includes the following line: ::

    [1542199843] [1367] Event broker module '/usr/lib64/centreon-engine/externalcmd.so' initialized successfully

if it does not, use the Central Server web interface to change the engine configuration with
the **Configuration > Pollers > Engine configuration** menu. Edit the Engine configuration,
go to the **Data** tab, add an **Event Broker** directive such as: ::

    /usr/lib64/centreon-engine/externalcmd.so

Then :ref:`Deploy configuration<deployconfiguration>`.
