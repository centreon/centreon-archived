===========================
Specific upgrade procedures
===========================

**************************************************
Upgrade a poller after an update to *Centreon* 2.4
**************************************************

This procedure explains how to update a poller's configuration after a
migration to *Centreon* 2.4. The given examples talk about *Nagios* but
this procedure should also work with *Centreon Engine* if you replace
binaries and pathes.

Poller modifications
====================

Create a ``centreon`` user with a password::

  $ useradd centreon
  $ passwd centreon

Add the ``nagios`` user to the ``centreon`` group::

  $ usermod -a -G centreon nagios

Open sudo's configuration file::

  $ visudo

Add the following line::

  User_Alias CENTREON=nagios,centreon
  
Then, update the existing configuration by replacing ``nagios`` by
``CENTREON``::

  CENTREON ALL=NOPASSWD: /etc/init.d/nagios restart
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios stop
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios start
  CENTREON ALL=NOPASSWD: /etc/init.d/nagios reload
  CENTREON ALL=NOPASSWD: /usr/bin/nagiostats
  CENTREON ALL=NOPASSWD: /usr/local/etc/bin/nagios *

Save your modifications and close the file.

Change the permissions of the directory containing *Nagios*'
configuration files::

  $ chown centreon:centreon </nagios/path/etc/>
  $ chmod 775 </nagios/path/etc/>

Also change the permissions of the *service-perfdata* file::

  $ chown centreon:centreon </nagios/path/var/>service-perfdata
  $ chmod 775 </nagios/path/var/>service-perfdata

Finally, it is necessary to validate *Centreon* is able to manage the
poller by exporting the configuration and by restarting the monitoring
engine through the web interface.

You should see *Nagios* has received a restart instruction by looking at
its log file.

Central modifications
=====================

Copy the SSH public key of the ``centreon`` user to the poller::

  $ su - centreon
  $ ssh-copy-id -i ~/.ssh/id_rsa.pub centreon@<poller_ip_address>

Replace ``<poller_ip_address>`` with the appropriate value.

To finalize the operation, connect to the poller server from the
central one::

  $ su - centreon
  $ ssh <poller_ip_address>

Answer ``y`` to the asked question. You should be able to connect
without beeing prompted for a password.
