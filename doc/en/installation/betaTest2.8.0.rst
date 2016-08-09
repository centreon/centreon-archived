.. _betaTest2_8_0: 

=====================
CES v3.4 installation
=====================

The CES v3.4 includes Centreon web 2.8, Centreon Engine 1.6, Centreon Broker 3.0
and are based on CentOS 6 operating system.

Prerequisites
=============

The prerequisites for Centreon Web 2.8 are evolved. It is strongly recommended 
to follow the instructions to set up your platform:
* Apache = 2.2
* Centreon Engine >= 1.6.0
* Centreon Broker >= 3.0.0
* CentOS = 6.x ou RedHat >= 6.x
* MariaDB = 5.5.47 ou MySQL = 5.1.73
* Net-SNMP = 5.5
* PHP >= 5.3.0
* Qt = 4.7.4
* RRDtools = 1.4.7

To install and update
=====================

This chapter describes how to set up a Centreon Web v2.8 platform. **This version is a Beta version**.
**So please do not use this version on your production envirnment!!!***.

.. warning::
    This procedure is based on CES context. All commands are for Red Hat and CentOS using yum.

Add the testing repo
--------------------

We added a new testing repo to allow to install **Beta** version.
With this repo you can install Centreon Web 2.8, Centreon Engine 1.6 and Centreon Broker 3.0
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
menui, select all poller and click on the **Apply configuration button**

.. note::
    La generate page was removed from Centreon web. You have to select your poller and to click on the new icon.
									 
Restart all Centreon components on all poller
*********************************************

Start Centreon Broker and Centreon Engine on **all poller**::

    # /etc/init.d/centengine start
    # /etc/init.d/cbd start

The identified risks during update
==================================

To reduce risks and issues during update to Centreon Web 2.8 linked to Centreon
Engine 1.6 and Centreon Broker 3.0 we shared to you a list of known issues.
Please check this points during and after your upgrade.

Known issues 
************

* Scales in peformance graphs display too many steps
* PHP Warning issues when user access to performance graphs menu in Centreon Web
* Some incompatibilities with Centreon modules already installed

Let's go!
*********

We are waiting your feedback on your `github <https://github.com/centreon/centreon>`_ project.
Please use the **"BetaTest** category in github to merge all issues from this beta test campaign.

If you have any question regarding CES 3.4 best test campaign you can send us an email at: centreon-beta-test@centreon.com

