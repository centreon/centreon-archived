*************
Configuration
*************

.. note::
    To get the IP address of your Centreon server, uses the command:
    ::
    
        # ip addr

Log in to Centreon web interface via the URL: http://[SERVER_IP]/centreon.
The Centreon setup wizard is displayed. Click on **Next**.

.. image:: /images/user/acentreonwelcome.png
   :align: center
   :scale: 85%

The Centreon setup wizard checks the availability of the modules. Click on **Next**.

.. image:: /images/user/acentreoncheckmodules.png
   :align: center
   :scale: 85%

Click on **Next**.

.. image:: /images/user/amonitoringengine2.png
   :align: center
   :scale: 85%

Click on **Next**.

.. image:: /images/user/abrokerinfo2.png
   :align: center
   :scale: 85%

Provide the information on the admin user, then click on **Next**.

.. image:: /images/user/aadmininfo.png
   :align: center
   :scale: 85%

By default, the *localhost* server is defined, the database root user is set to *root* and the root password is empty.
If you use a remote database server, change these entries.
In this case, you only need to define a password for the user accessing the Centreon databases, i.e., *Centreon*. Click on **Next**.

.. image:: /images/user/adbinfo.png
   :align: center
   :scale: 85%

.. note::
    If the **"Add innodb_file_per_table=1 in my.cnf file under the [mysqld] section and restart MySQL Server."**
    error message appears, perform the following operations:
    
    1. Log in to the *root* user on your server.
    
    2. Modify this file::
    
        /etc/my.cnf
    
    3. Add these lines to the file::
    
        [mysqld]
        innodb_file_per_table=1
    
    4. Restart the mysql service::

        # systemctl restart mysql
    
    5. Click on **Refresh**.

.. note::
    If you use a deported MySQL v8.x DBMS, you may have the following error message: *error*.
    Please have a look to this :ref:`procedure` to solve this issue.

The Centreon setup wizard configures the databases. Click on **Next**.

.. image:: /images/user/adbconf.png
   :align: center
   :scale: 85%

You will now be able to install the Centreon server modules.

Click on **Install**.

.. image:: /images/user/module_installationa.png
   :align: center
   :scale: 85%

Once installation is complete, click on **Next**.

.. image:: /images/user/module_installationb.png
   :align: center
   :scale: 85%

At this point, an advertisement informs you of the latest Centreon news and products. 
If your platform is connected to the internet, the information you receive will be up to date.
If you are not online, only information on the current version will be displayed.

.. image:: /images/user/aendinstall.png
   :align: center
   :scale: 85%

The installation is complete. Click on **Finish**.

You can now log in.

.. image:: /images/user/aconnection.png
   :align: center
   :scale: 65%
