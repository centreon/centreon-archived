**********************
Pre-installation steps
**********************

First, *SELinux* should be disabled. To do this, you have to edit the file
*/etc/selinux/config* and replace *enforcing* by *disabled*::

    SELINUX=disabled

.. note::
    After saving the file, reboot your operating system to apply the changes.

Perform a quick check of the SELinux status::

    $ getenforce
    Disabled

*************************
Installing the repository
*************************

Redhat Software Collections Repository
--------------------------------------

To install Centreon you will need to set up the official software collections repository supported by Redhat.

.. note::
    Software collections are required for installing PHP 7 and associated libraries (Centreon requirement).

Install the software collections repository using this command::

   # yum install centos-release-scl

The repository is now installed.

Centreon repository
-------------------

To install Centreon software from the repository, you should first install the
centreon-release package, which will provide the repository file.

Install the Centreon repository using this command::

    # yum install -y http://yum.centreon.com/standard/19.10/el7/stable/noarch/RPMS/centreon-release-19.10-1.el7.centos.noarch.rpm

The repository is now installed.

.. note::
    Some may not have the wget package installed. If not perform the following:
    ::

        # yum install wget

************************************
Installing a Centreon Central Server
************************************

This section describes how to install a Centreon central server.

Installing a Centreon Central Server with database
--------------------------------------------------

Run the command::

    # yum install centreon
    # systemctl restart mysql

.. include:: common/sql_strict_mode.rst

Installing a Centreon Central Server without database
-----------------------------------------------------

Run the command::

    # yum install centreon-base-config-centreon-engine

.. _dedicateddbms:

Installing the DBMS on the dedicated server
-------------------------------------------

Run the commands::

    # yum install centreon-database
    # systemctl daemon-reload
    # systemctl restart mysql

.. note::
    **centreon-database** package installs a database server optimized for use with Centreon.

Then create a distant **root** account: ::

    mysql> CREATE USER 'root'@'IP' IDENTIFIED BY 'PASSWORD';
    mysql> GRANT ALL PRIVILEGES ON *.* TO 'root'@'IP' WITH GRANT OPTION;
    mysql> FLUSH PRIVILEGES;

.. note::
    Replace **IP** by the public IP address of the Centreon server and **PASSWORD**
    by the **root** password.

.. warning::
    MySQL >= 8 require a strong password. Please use uppercase, numeric and special characters; or uninstall the
    validate_password using following command: ::
        
        mysql> uninstall plugin validate_password;

.. warning::
    When running a PHP version before 7.1.16, or PHP 7.2 before 7.2.4, set MySQL 8 Server's default password plugin to
    mysql_native_password or else you will see errors similar to *The server requested authentication method unknown
    to the client [caching_sha2_password]* even when caching_sha2_password is not used.
    
    This is because MySQL 8 defaults to caching_sha2_password, a plugin that is not recognized by the older PHP
    releases. Instead, change it by setting *default_authentication_plugin=mysql_native_password* in **my.cnf**.
    
    Change the method to store the password using following command: ::
    
        mysql> ALTER USER 'root'@'IP' IDENTIFIED WITH mysql_native_password BY 'PASSWORD';
        mysql> FLUSH PRIVILEGES;

.. include:: common/sql_strict_mode.rst

Once the installation is complete you can delete this account using: ::
        
    mysql> DROP USER 'root'@'IP';

Database management system
--------------------------

We recommend using MariaDB for your database because it is open source. Ensure
the database server is available to complete the installation (locally or no).

It is necessary to modify **LimitNOFILE** limitation. Do not try to set this
option in **/etc/my.cnf** because it will *not* work. Run the commands:

**For MariaDB**: ::

    # mkdir -p  /etc/systemd/system/mariadb.service.d/
    # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
    # systemctl daemon-reload
    # systemctl restart mysql

**For MySQL**: ::

    # mkdir -p  /etc/systemd/system/mysqld.service.d
    # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mysqld.service.d/limits.conf
    # systemctl daemon-reload
    # systemctl restart mysqld

Setting the PHP time zone
-------------------------

You are required to set the PHP time zone. Run the command::

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php72/php.d/php-timezone.ini

.. note::
    Change **Europe/Paris** to your time zone. You can find the supported list
    of time zone `here <http://php.net/manual/en/timezones.php>`_.

After saving the file, please do not forget to restart the PHP-FPM server::

    # systemctl restart rh-php72-php-fpm

Configuring/disabling the firewall
----------------------------------

Add firewall rules or disable the firewall by running the following commands::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

Launching services during system bootup
---------------------------------------

To make services start automatically during system bootup, run these commands on the central server::

    # systemctl enable httpd24-httpd
    # systemctl enable snmpd
    # systemctl enable snmptrapd
    # systemctl enable rh-php72-php-fpm
    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine
    # systemctl enable centreon

.. note::
    If the MariaDB database is on a dedicated server, execute this command
    on the database server: ::
        
        # systemctl enable mysql
    
    or for Mysql: ::
        
        # systemctl enable mysqld

Concluding the installation
---------------------------

Before starting the web installation process, you will need to execute the following commands::

    # systemctl start rh-php72-php-fpm
    # systemctl start httpd24-httpd
    # systemctl start mysqld
    # systemctl start centreon
    # systemctl start snmpd
    # systemctl start snmptrapd
