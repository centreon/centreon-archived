.. _betaTest2_7_0: 

=====================
CES v3.3 installation
=====================

The CES v3.3 includes Centreon web 2.7, Centreon Engine 1.5, Centreon Broker 2.11
and are based on CentOS 6 operating system.

.. warning::
    **This version is not yet supported by Centreon helpdesk!!!**. Do not install
    this Release Candidate in production. We are not responsible for damages 
   that may result when used it on a production platform.

Prerequisites
=============

The prerequisites for Centreon web 2.7 are evolved. It is strongly recommended 
to follow the instructions to set up your platform:
* Apache = 2.2
* Centreon Engine >= 1.5.0
* Centreon Broker >= 2.11.0
* CentOS = 6.x ou RedHat >= 6.x
* MariaDB = 5.5.35 ou MySQL = 5.1.73
* Net-SNMP = 5.5
* PHP >= 5.3.0
* Qt = 4.7.4
* RRDtools = 1.4.7

To install and update
=====================

This chapter describes how to set up a Centreon web v2.7 platform. **This version is a Release Candidate version**.
**So please do not use this version on your production envirnment!!!***.

.. warning::
    This procedure is based on CES context. All commands are for Red Hat and Cent OS using yum.

Add the testing repo
--------------------

We added a new testing repo to allow to install **Release Candidate** version.
With this repo you can install Centreon 2.7, Centreon Engine 1.5 and Centreon Broker 2.11
and some additional new widgets.

If you want to set up a new platform or update an existing platform you need this
new repo on your server.

To install this repo use the following commands::

   # cd /etc/yum.repos.d
   # wget http://yum.centreon.com/standard/3.0/testing/ces-standard-testing.repo -O /etc/yum.repos.d/ces-standard-testing.repo

Install
-------

If you install a new platform, please see the `regular documentation<install_from_packages>`.

Update
------

If you want to use an existing platform, please follow instructions

Stop Centreon components
************************

.. warning::
    Before to start the update, check if you don't have any Centreon-Broker retention files.

Stop Centreon Broker and Centreon Engine on **all poller**::

   # /etc/init.d/centengine stop
   # /etc/init.d/cbd stop

Update components
*****************

   ::

   # yum update centreon

Restart web server 
******************

Due to the installation of PHP-intl it is necessary to restart the Apache web server
to load new extension.

   ::

   # /etc/init.d/httpd restart

Conclude update via Centreon web interface
******************************************

Connect to your Centreon web interface and follow instructions to update Centreon's 
databases. During this process a new configuration file will be created.

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

The identified risks during update
==================================

To reduce risks and issues during update to Centreon web 2.7 linked to Centreon
Engine 1.5 and Centreon Broker 2.11 we shared to you a list of known issues.
Please check this points during and after your upgrade.

Known issues 
************

* Dependency issue between Centreon Engine and Centreon Broker because this two components (Centreon Broker 2.11.0 and Centreon Engine 1.5.0) are prerequisites for Centreon web 2.7.0
* Update databases global schema issue
* Database engine update from MyISAM to InnoDB (expected logs and data_bin tables)
* Update hostgroup and servicegroup tables schemas
* The Centreon Broker temporaries and failovers are now manage by Centreon web by default. It may have a conflict with existing configuration of Centreon Broker. Please check the configuration and logs of all Centreon Broker to be sure that all broker are running and no data are lost.
* Browser cache issue: you have to clean browser cache after Centreon web migration and just after first connection.
* PHP dependency issue: a new PHP component is needed by Centreon web interface. You have to restart Apache web server.
* Incompatibility with Centreon modules already installed. Since v2.7.0 version Centreon web interface have a new look. If you have modules please don't upgrade Centreon web.
* Generation of configuration issue: the Centreon configuration generation engine was entirely rewritten. There is therefore a risk of errors in the exported configurations
* Abrupt change from NDOutils to Centreon Broker during Centreon web 2.7.0 update. Centreon web 2.7.0 is no more compatible with Nagios and NDOutils. Numerus issues will appear if you want to update your platform based on Nagios and NDOutils.

Let's go!
*********

We are waiting your feedback on your `github <https://github.com/centreon/centreon>`_ project.
Please use the **"BetaTest** category in github to merge all issues from this beta test campaign.

If you have any question regarding CES 3.3 best test campaign you can send us an email at: centreon-beta-test@centreon.com
