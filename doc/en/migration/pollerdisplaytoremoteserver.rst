.. _migratefrompollerdisplay:

===============================================
Migration of a platform with the Poller Display
===============================================

**************************************
Migrating your Centreon central server
**************************************

If the module **centreon-poller-display-central-1.6.x** is installed:

1. Go to the **Administration > Extensions > Modules** menu and uninstall the
   **centreon-poller-display-central**.

2. Remove the associated package: ::

    # yum remove centreon-poller-display-central

If your server uses the CentOS or Red Hat v7 operating system, refer to the
:ref:`update procedure <upgrade_from_packages>` to update your Poller Display
server. Otherwise, refer to the :ref:`migration procedure <migrate_to_1810>`.

.. note::
    If you use the Centreon EMS modules, you must update these repositories. Be sure to contact Centreon support and request new licenses.

********************************************************************
Migrating a server from the Centreon Poller Display to Remote Server
********************************************************************

1. Go to the **Administration > Extensions > Modules** menu and uninstall the
   **Centreon Poller Display** module.

2. If you installed the module using an RPM package, remove it with the
   following command::

    # yum remove centreon-poller-display

.. note::
     If you use Centreon EMS modules, you must update the repositories. Be sure to contact your Centreon support and request new licenses.

3. If your server uses the CentOS or Red Hat v7 operating system, refer to the
   :ref:`update procedure <upgrade_from_packages>` to update your Poller Display
   server. Otherwise, refer to the :ref:`migration procedure <migrate_to_1810>`.

4. Go to **Administration > Extensions > Modules** menu and install the **centreon-license-manager** module.

5. Execute the following command: ::

     # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the Poller.

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success

      Set 'remote' instance type...Done

      Centreon Remote enabling finished.

6. Add rights to centreon database user to use **LOAD DATA INFILE** command::

    # mysql -h <database_server_address> -u root -p
    MariaDB [(none)]> GRANT FILE on *.* to 'centreon'@'<remote_server_ip>';

7. Exchange the SSH key. If you do not have a private SSH key for the Centreon user on the central server: ::

    # su - centreon
    $ ssh-keygen -t rsa

Copy this key to the new server: ::

    # su - centreon
    $ ssh-copy-id -i .ssh/id_rsa.pub centreon@IP_POLLER

8. **On the Centreon Central server**, edit all pollers and attach them to the
   **Remote Server** using the selection list.

.. note::
    Remember to :ref:`generate the configuration <deployconfiguration>` for your
    **Remote Server**.

.. note::
    A Centreon Remote Server is self-administered. Thus, the
    configuration of the LDAP directory, users and ACLs are specific to this server
    and must be configured through the **Administration** menu.
