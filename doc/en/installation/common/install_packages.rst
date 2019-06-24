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

    # yum install -y http://yum.centreon.com/standard/19.04/el7/stable/noarch/RPMS/centreon-release-19.04-1.el7.centos.noarch.rpm

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

Installing a Centreon Central Server without database
-----------------------------------------------------

Run the command::

    # yum install centreon-base-config-centreon-engine

Installing MySQL on the dedicated server
----------------------------------------

Run the commands::

    # yum install centreon-database
    # systemctl daemon-reload
    # systemctl restart mysql

.. note::
    **centreon-database** package installs a database server optimized for use with Centreon.

.. note::
    Centreon does **not** support the SQL STRICT mode yet. Please make sure that
    it is disabled. For more information on how to disable the mode please check
    the official `MariaDB documentation <https://mariadb.com/kb/en/library/sql-mode/#strict-mode>`_.

Then create a distant **root** account: ::

    MariaDB [(none)]> GRANT ALL PRIVILEGES ON *.* TO 'root'@'IP' IDENTIFIED BY 'PASSWORD' WITH GRANT OPTION;

.. note::
    Replace **IP** by the public IP address of the Centreon server and **PASSWORD**
    by the **root** password. Once the installation is complete you can delete this
    account using: ::
        
        MariaDB [(none)]> DROP USER 'root'@'IP';

Database management system
--------------------------

We recommend using MariaDB for your database because it is open source. Ensure
the database server is available to complete the installation (locally or no).

It is necessary to modify **LimitNOFILE** limitation. Do not try to set this
option in **/etc/my.cnf** because it will *not* work.

Run the commands::

   # mkdir -p  /etc/systemd/system/mariadb.service.d/
   # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
   # systemctl daemon-reload
   # systemctl restart mysql

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
    If the MySQL/MariaDB database is on a dedicated server, execute this command
    on the database server: ::
    
        # systemctl enable mysql

Concluding the installation
---------------------------

Before starting the web installation process, you will need to execute the following commands::

    # systemctl start rh-php72-php-fpm
    # systemctl start httpd24-httpd
    # systemctl start mysqld
    # systemctl start cbd
    # systemctl start snmpd
    # systemctl start snmptrapd
