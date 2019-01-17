**********************
Pre-installation steps
**********************

*SELinux* should be disabled. To do this, you first have to edit the file
*/etc/selinux/config* and replace "enforcing" by "disabled"::

    SELINUX=disabled

.. note::
    After saving the file, please reboot your operating system to apply the changes.

A quick check of SELinux status::

    $ getenforce
    Disabled

*************************
Installing the repository
*************************

Redhat Software collections repository
--------------------------------------

To install Centreon you will need to set up the official software collections repository supported by Redhat.

.. note::
    Software collections are required in order to install PHP 7 and associated libs (Centreon requirement).

Software collections repository installation::

   # yum install centos-release-scl

The repository is now installed.

Centreon repository
-------------------

To install Centreon software from the repository, you should first install the
centreon-release package which will provide the repository file.

Centreon repository installation::

   # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
   # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

The repository is now installed.

************************************
Installing a Centreon central server
************************************

This chapter describes the installation of a Centreon central server.

Installing Centreon central server with database
------------------------------------------------

Run the command::

    # yum install centreon
    # systemctl restart mysql

Installing Centreon central server without database
---------------------------------------------------

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

Database management system
--------------------------

The MySQL database server should be available to complete installation (locally or not). MariaDB is recommended.

It is necessary to modify **LimitNOFILE** limitation.
Setting this option into /etc/my.cnf will NOT work.

Run the commands::

   # mkdir -p  /etc/systemd/system/mariadb.service.d/
   # echo -ne "[Service]\nLimitNOFILE=32000\n" | tee /etc/systemd/system/mariadb.service.d/limits.conf
   # systemctl daemon-reload
   # systemctl restart mysql

Setting the PHP timezone
------------------------

You must set the PHP timezone. Perform the command::

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php71/php.d/php-timezone.ini

.. note::
    Change **Europe/Paris** to your timezone.

After saving the file, please do not forget to restart the apache server::

    # systemctl restart httpd

Configuring/disabling the firewall
----------------------------------

Add firewall rules or disable the firewall by running following commands::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

Launching services during system bootup
---------------------------------------

To make services automatically start during system bootup run these commands on the central server::

    # systemctl enable httpd
    # systemctl enable snmpd
    # systemctl enable snmptrapd
    # systemctl enable rh-php71-php-fpm
    # systemctl enable centcore
    # systemctl enable centreontrapd
    # systemctl enable cbd
    # systemctl enable centengine

.. note::
    If MySQL database is on a dedicated server, execute the enable command of mysql on the database server.

Concluding the installation
---------------------------

Before starting the web installation process, you will need to execute::

    # systemctl start rh-php71-php-fpm
    # systemctl start httpd
    # systemctl start mysqld
    # systemctl start cbd
    # systemctl start snmpd
    # systemctl start snmptrapd
