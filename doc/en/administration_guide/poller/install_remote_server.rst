=======================
Install a Remote Server
=======================

------------
Installation
------------

The installation of a Remote Server is quite similar to install a Centreon
Central server.

Using Centreon ISO
------------------

Follow the :ref:`Using Centreon ISO el7<installisoel7>` documentation to install a Centreon
Central server. 

Using Centreon packages
-----------------------

Follow the :ref:`Using packages<install_from_packages>` documentation to install a Centreon
Central Server.

---------------------------
Enable Remote Server option
---------------------------

Connect to your **Remoter Server** and execute following command::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the poller

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success
      
      Set 'remote' instance type...Done
      
      Centreon Remote enabling finished.

Go to :ref:`SSH Key Exchange chapter to continu<sskkeypoller>`.
