.. _upgrade_from_packages:

===========================
Upgrading to Centreon 18.10
===========================

This chapter describes how to upgrade your platform to Centreon 18.10.

.. warning::
    At the end of this procedure, Centreon EMS users will have to request new
    licenses to `Centreon support <https://centreon.force.com>`_.

.. warning::
    This procedure only applies on Centreon platform installed from Centreon 3.4
    packages on **Red Hat / CentOS version 7** distributions.
    
    If this is not the case, refer to the :ref:`migration<upgradecentreon1810>`
    procedure.

To upgrade your Centreon MAP server, refer to the `associated documentation
<https://documentation.centreon.com/docs/centreon-map-4/en/latest/upgrade/index.html>`_.

To upgrade your Centreon MBI server, refer to the `associated documentation
<https://documentation.centreon.com/docs/centreon-bi-2/en/latest/update/index.html>`_.

*******************
Performing a backup
*******************

Be sure that you have fully backed up your environment for the following
servers:

* Central server
* Database server

********************************
Centreon Central Server Upgrade
********************************

Upgrading the repository
========================

To install Centreon you will need to set up the official software collections
repository supported by Redhat.

.. note::
    *Software collections* are required in order to install PHP 7 and associated
    libs (Centreon requirement).

Run the following command: ::

    # yum install centos-release-scl

Upgrading the Centreon repository.

Run the following command: ::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

Updating the Centreon solution
==============================

Upgrade all components: ::

    # yum update centreon*

.. note::
    Accept new GPG keys from repositories as needed.

Complementary actions
=====================

PHP timezone needs to be set. Perform the command: ::

    # echo "date.timezone = Europe/Paris" > /etc/opt/rh/rh-php71/php.d/php-timezone.ini

.. note::
    Change **Europe/Paris** to your timezone.

Restart the services by running the following commands: ::

    # systemctl enable rh-php71-php-fpm
    # systemctl start rh-php71-php-fpm
    # systemctl restart httpd
    # systemctl restart cbd
    # systemctl restart centengine

Finalizing the upgrade
======================

Log into Centreon web interface to continue upgrade process:

Click on **Next**:

.. image:: /_static/images/upgrade/web_update_1.png
    :align: center

Click on **Next**:

.. image:: /_static/images/upgrade/web_update_2.png
    :align: center

The release notes describes main changes, click on **Next**:

.. image:: /_static/images/upgrade/web_update_3.png
    :align: center

The process performs the various upgrades, click on **Next**:

.. image:: /_static/images/upgrade/web_update_4.png
    :align: center

Your Centreon server is now up to date, click on **Finish** to access to log in
page:

.. image:: /_static/images/upgrade/web_update_5.png
    :align: center

To upgrade your Centreon BAM module, refer to the `associated documentation
<https://documentation.centreon.com/docs/centreon-bam/en/latest/update/index.html>`_.

*********************
Upgrading the Pollers
*********************

Upgrading the repository
========================

Run the following command: ::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

Upgrading the Centreon solution
===============================

Upgrade all components: ::

    # yum update centreon*

.. note::
    Accept new GPG keys from repositories as needed.

Complementary actions
=====================

Restart the services by executing the following commands: ::

    # systemctl restart cbd
    # systemctl restart centengine

******************************
Centreon Poller Displat update
******************************

Refer to the :ref:`migration procedure for Poller Display <migratefrompollerdisplay>`.
