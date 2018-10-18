.. _migratefrompollerdisplay:

===========================================
Migration of a platform with Poller Display
===========================================

*****************************
Migrate your Centreon Central
*****************************

If the module **centreon-poller-display-central-1.6.x** is installed:

1. Go to **Administration > Extensions > Modules** menu and uninstall the
   **centreon-poller-display-central**

2. Remote the associated package: ::

    # yum remove centreon-poller-display-central

If your server use a CentOS or Red Hat v7 operating system, refer to the
:ref:`update procedure <upgrade_from_packages>` to update your Poller Display
server; else refer to the :ref:`migration procedure <migrate_to_1810>`.

.. note::
    If you have Centreon EMS modules, it is necessary to update these repositories.
    Contact your Centreon support for these. Then ask new licenses for those.

****************************************************************
Migration a server from Centreon Poller Display to Remote Server
****************************************************************

1. Go to the **Administration > Extensions > Modules** menu and uninstall the
   **Centreon Poller Display** module.

2. If the module was installed using RPM package, remove this one using the
   following command::

    # yum remove centreon-poller-display

.. note::
     If you have Centreon EMS modules, it is necessary to update these repositories.
    Contact your Centreon support for these. Then ask new licenses for those.

3. If your server use a CentOS or Red Hat v7 operating system, refer to the
   :ref:`update procedure <upgrade_from_packages>` to update your Poller Display
   server; else refer to the :ref:`migration procedure <migrate_to_1810>`.

4. Go to **Administration > Extensions > Modules** menu and install the
    **centreon-license-manager** module.

5. Execute the following command: ::

     # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the poller

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:
      
      Limiting Menu Access...Success
      Limiting Actions...Done
      
      Notifying Master...Success
     Centreon Remote enabling finished.

6. Go to :ref:`SSH Key Exchange chapter to continu<sskkeypoller>` to perform
   exchange between your **Remote Server** and all attached **pollers**.

7. **On the Centreon Central server**, edit all pollers and attach them to the
   **Remote Server** using select list.

.. note::
    Do not forget to :ref:`generate configuration <deployconfiguration>` of your
    **Remote Server**.

.. note::
    A Centreon Remote Server is a server that is self-administering. Thus, the
    configuration of the LDAP directory, users and ACLs is specific to this server
    and must be configured via the **Administration** menu.
