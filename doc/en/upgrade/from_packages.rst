.. _upgrade_from_packages:

=================
Upgrade using RPM
=================

The CES v3.3 includes Centreon web 2.7, Centreon Engine 1.5, Centreon Broker 2.11 and are based on CentOS 6 operating system.

.. warning::
   This release is not yet compatible with other commercial products
   from Centreon, like Centreon MBI, Centreon BAM or Centreon Map.
   If your are using any of these products, you are strongly advised
   **NOT** to update Centreon Web until new releases of the forementioned
   products are available and specifically mention Centreon Web 2.8
   compatibility.

Prerequisites
=============

The prerequisites for Centreon web 2.7 are evolved. It is strongly recommended
to follow the instructions to set up your platform:
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

*******
Upgrade
*******

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
menu and click on the generate configuration icon (new icon).

.. note::
    La generate page was removed from Centreon web. You have to select your poller and to click on the new icon.

Restart all Centreon components on all poller
*********************************************

Start Centreon Broker and Centreon Engine on **all poller**::

   # /etc/init.d/centengine start
   # /etc/init.d/cbd start


Then, if all is ok, go on the Centreon interface and log out and follow the steps :

The identified risks during update
==================================

To reduce risks and issues during update to Centreon web 2.7 linked to Centreon
Engine 1.5 and Centreon Broker 2.11 we shared to you a list of known issues.
Please check this points during and after your upgrade.

Known issues
************

* Dependency issue between Centreon Engine and Centreon Broker because this two components (Centreon Broker 2.11.0 and Centreon Engine 1.5.0) are prerequisites for Centreon web 2.7.0
* Update databases global schema issue
* Change database engine from MyISAM to InnoDB for all tables (except logs and data_bin tables)
* Update hostgroup and servicegroup tables schemas
* The Centreon Broker temporaries and failovers are now manage by Centreon web by default. It may have a conflict with existing configuration of Centreon Broker. Please check the configuration and logs of all Centreon Broker to be sure that all broker are running and no data are lost.
* Browser cache issue: you have to clean browser cache after Centreon web migration and just after first connection.
* PHP dependency issue: a new PHP component is needed by Centreon web interface. You have to restart Apache web server.
* Incompatibility with Centreon modules already installed. Since v2.7.0 version Centreon web interface have a new look. If you have modules please don't upgrade Centreon web.
* Generation of configuration issue: the Centreon configuration generation engine was entirely rewritten. There is therefore a risk of errors in the exported configurations
* Abrupt change from NDOutils to Centreon Broker during Centreon web 2.7.0 update. Centreon web 2.7.0 is no more compatible with Nagios and NDOutils. Numerus issues will appear if you want to update your platform based on Nagios and NDOutils.
