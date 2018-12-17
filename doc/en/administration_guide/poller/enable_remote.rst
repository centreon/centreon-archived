***************************
Enable Remote Server option
***************************

Connect to your **Remoter Server** and execute following command::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

.. note::
    Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the poller.
    You can define multiple IP address using a coma as separator.

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success
      
      Set 'remote' instance type...Done
      
      Centreon Remote enabling finished.
