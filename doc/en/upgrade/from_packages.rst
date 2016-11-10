.. _upgrade_from_packages:

=================
Upgrade using RPM
=================

Centreon Entreprise Server (CES) v3.4 includes Centreon Web 2.8, Centreon Engine 1.6, Centreon Broker 3.0.
It comes in two operating system flavors, either CentOS 6 or CentOS 7.

.. warning::
   This release is not yet compatible with other commercial products
   from Centreon, like Centreon MBI, Centreon BAM or Centreon Map.
   If your are using any of these products, you are strongly advised
   **NOT** to update Centreon Web until new releases of the forementioned
   products are available and specifically mention Centreon Web 2.8
   compatibility. A notable exception to this notice is EMS/EPP.

Prerequisites
=============

The prerequisites for Centreon Web 2.8 have evolved. It is strongly recommended
to follow the instructions to set up your platform.

**Centreon advises you to use MariaDB** instead of MySQL.

+----------+-----------+
| Software | Version   |
+==========+===========+
| MariaDB  | >= 5.5.35 |
+----------+-----------+
| MySQL    | >= 5.1.73 |
+----------+-----------+

Dependent software
==================

The following table describes the dependent software:

+----------+-----------+
| Software | Version   |
+==========+===========+
| Apache   | 2.2       |
+----------+-----------+
| GnuTLS   | >= 2.0    |
+----------+-----------+
| Net-SNMP | 5.5       |
+----------+-----------+
| openssl  | >= 1.0.1e |
+----------+-----------+
| PHP      | >= 5.3.0  |
+----------+-----------+
| Qt       | >= 4.7.4  |
+----------+-----------+
| RRDtools | 1.4.7     |
+----------+-----------+
| zlib     | 1.2.3     |
+----------+-----------+

CES repository upgrade
======================

If you are already a CES user, you need to update your CES .repo file to
get software that is part of CES 3.4 (namely Centreon Web 2.8 and
associated components). Run the commands for your operating system.

CentOS 6
********

::

   $ rm -f /etc/yum.repos.d/ces-standard.repo
   $ wget http://yum.centreon.com/standard/3.4/el6/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


CentOS 7
********

::

   $ rm -f /etc/yum.repos.d/ces-standard.repo
   $ wget http://yum.centreon.com/standard/3.4/el7/stable/centreon-stable.repo -O /etc/yum.repos.d/centreon-stable.repo


Core components upgrade
=======================

Stop Centreon components
************************

.. warning::
    Before to start the update, check if you don't have any Centreon-Broker retention files.

Stop Centreon Broker and Centreon Engine on **all poller**::

   # /etc/init.d/centengine stop
   # /etc/init.d/cbd stop

Update components
*****************

In order to update the Centreon monitoring interface, simply run the following command:

 ::

 # yum update centreon

.. warning::
   If you encounter dependency problems with centreon-engine-webservices, please remove this RPM that is now deprecated. Run the following line:
   # yum remove centreon-engine-webservices

If you come from Centreon 2.7.0-RC2, in order to avoid the rpm naming problem please launch the following command line:

  ::

  # yum downgrade centreon-2.7.0 centreon-plugins-2.7.0 centreon-base-config-centreon-engine-2.7.0 centreon-plugin-meta-2.7.0 centreon-common-2.7.0 centreon-web-2.7.0 centreon-trap-2.7.0 centreon-perl-libs-2.7.0


Restart web server
******************

Due to the installation of PHP-intl it is necessary to restart the Apache web server
to load new extension.

 ::

   # /etc/init.d/httpd restart

Conclude update via Centreon web interface
******************************************

Connect to your Centreon web interface and follow instructions to update Centreon's databases. During this process a new configuration file will be created.

Presentation
------------

.. image:: /_static/images/upgrade/step01.png
   :align: center

Check dependencies
------------------

This step checks the dependencies on php modules.

.. image:: /_static/images/upgrade/step02.png
   :align: center

Release notes
-------------

.. image:: /_static/images/upgrade/step03.png
   :align: center

Upgrade the database
--------------------

This step upgrades database model and data, version by version.

.. image:: /_static/images/upgrade/step04.png
   :align: center

Finish
------

.. image:: /_static/images/upgrade/step05.png
   :align: center

Generate and export configuration to all poller
***********************************************

To conclude the installation it is necessary to generate Centreon Engine and
Centreon Broker configuration. To perform this operation go to **Configuration > Poller**
menu and click on the generate configuration icon.

Restart all Centreon components on all poller
*********************************************

Start Centreon Broker and Centreon Engine on **all poller**::

   # /etc/init.d/centengine start
   # /etc/init.d/cbd start


Then, if all is ok, go on the Centreon interface and log out and follow the steps :

EMS/EPP upgrade
===============

.. note::
   Not a EMS/EPP user ? You might still find Centreon Plugin Packs very
   useful to configure your monitoring system in minutes. You will find
   installation guidance in the :ref:`online documentation <installation_ppm>`.


If you use additional Centreon modules you might need to update them too,
for them to work properly with your new Centreon version. This is
particularly true for EMS/EPP users.

Repository update
*****************

Just like for CES, the .repo file needs to be updated to use the 3.4
release. Please ask Centreon support team if you do not know how to
perform this operation.

Package update
**************

Run the following command on your central server to update Centreon
Plugin Pack Manager, the Plugin Packs and their associated plugins.

::

   # yum update centreon-pp-manager ces-plugins-* ces-pack-*


You will also need to run the following command on every poller using
the Plugin Packs.

::

   # yum update ces-plugins-*


Web update
**********

You now need to run the web update manually. For this purpose, go to
Administration -> Extensions -> Modules.

.. image:: /_static/images/upgrade/ppm_1.png
   :align: center

Install Centreon License Manager (PPM dependency) and update Centreon
Plugin Pack Manager.

.. image:: /_static/images/upgrade/ppm_2.png
   :align: center

Good, your module is working again !

The identified risks during update
==================================

To reduce risks and issues during update to Centreon Web 2.8 linked to Centreon
Engine 1.6 and Centreon Broker 3.0 we shared to you a list of known issues.
Please check this points during and after your upgrade.

Known issues
************

* Not compatible with most commercial products : Centreon MBI, Centreon BAM and Centreon Map are not yet compatible with Centreon Web 2.8.
* Dependency issue between Centreon Engine and Centreon Broker because this two components (Centreon Broker 3.0 and Centreon Engine 1.6) are prerequisites for Centreon Web 2.8
* Update databases global schema issue
* Change database engine from MyISAM to InnoDB for all tables (except logs and data_bin tables)
* Update hostgroup and servicegroup tables schemas
* The Centreon Broker temporaries and failovers are now manage by Centreon web by default. It may have a conflict with existing configuration of Centreon Broker. Please check the configuration and logs of all Centreon Broker to be sure that all broker are running and no data are lost.
* Browser cache issue: you have to clean browser cache after Centreon web migration and just after first connection.
* PHP dependency issue: a new PHP component is needed by Centreon web interface. You have to restart Apache web server.
* Incompatibility with Centreon modules already installed. Since v2.7.0 version Centreon web interface have a new look. If you have modules please don't upgrade Centreon web.
* Generation of configuration issue: the Centreon configuration generation engine was entirely rewritten. There is therefore a risk of errors in the exported configurations
* Abrupt change from NDOutils to Centreon Broker during Centreon web 2.7.0 update. Centreon web 2.7.0 is no more compatible with Nagios and NDOutils. Numerus issues will appear if you want to update your platform based on Nagios and NDOutils.
