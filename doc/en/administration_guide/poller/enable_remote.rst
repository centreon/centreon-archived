*********************************
Enabling the Remote Server option
*********************************

Connect to your **Remoter Server** and execute following command::

    # /usr/share/centreon/bin/centreon -u admin -p centreon -a enableRemote -o CentreonRemoteServer -v '@IP_CENTREON_CENTRAL;<not check SSL CA on Central>;<HTTP method>;<TCP port>;<not check SSL CA on Remote>;<no proxy to call Central>'

Replace **@IP_CENTREON_CENTRAL** by the IP of the Centreon server seen by the
poller. You can define multiple IP address using a coma as separator.

.. note::
    * To use HTTPS, replace **@IP_CENTREON_CENTRAL** by
      **https://@IP_CENTREON_CENTRAL**.
    * To use non default port, replace **@IP_CENTREON_CENTRAL** by
      **@IP_CENTREON_CENTRAL:<port>**.

For the **<not check SSL CA on Central>** option you can put **1** to do not
check the SS CA on the Centreon Central Server if HTTPS is enabled, or put **0**.

The **<HTTP method>** is to define how the Centreon Central server can contact
the Remote server: HTTP or HTTPS.

The **<TCP port>** is to define on wich TCP port the entreon Central server can
contact the Remote server.

For the **<not check SSL CA on Remote>** option you can put **1** to do not
check the SS CA on the Remote server if HTTPS is enabled, or put **0**.

For the **<no proxy to call Central>** option you can put **1** to do not
use HTTP(S) proxy to contact the Centreon Central server.

This command will enable **Remote Server** mode::

    Starting Centreon Remote enable process:
    Limiting Menu Access...               Success
    Limiting Actions...                   Done
    Authorizing Master...                 Done
    Set 'remote' instance type...         Done
    Notifying Master...
    Trying host '10.1.2.3'... Success
    Centreon Remote enabling finished.

Add rights to centreon database user to use **LOAD DATA INFILE** command::

    # mysql -u root -p
    MariaDB [(none)]> GRANT FILE on *.* to 'centreon'@'localhost';
