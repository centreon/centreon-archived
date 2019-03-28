*********************************
Enabling the Remote Server option
*********************************

Connect to your **Remoter Server** and execute following command::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v @IP_CENTREON_CENTRAL

Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the
poller. You can define multiple IP address using a coma as separator.

.. note::
    * To use HTTPS, replace **@IP_CENTREON_CENTRAL** by
      **https://@IP_CENTREON_CENTRAL**.
    * To use non default port, replace **@IP_CENTREON_CENTRAL** by
      **@IP_CENTREON_CENTRAL:<port>**.
    * To disable SSL certificate validation, replace **@IP_CENTREON_CENTRAL**
      by **@IP_CENTREON_CENTRAL;1**

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:

      Limiting Menu Access...Success
      Limiting Actions...Done

      Notifying Master...Success
      
      Set 'remote' instance type...Done
      
      Centreon Remote enabling finished.
