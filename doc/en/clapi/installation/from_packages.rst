==============
Using packages
==============

Centreon provides RPM for its products through Centreon Entreprise
Server (CES). Open source products are available for free from our
repository.

These packages have been successfully tested with CentOS 5 and RedHat 5.

*************
Prerequisites
*************

In order to use RPM from the CES repository, you have to install the
appropriate repo file. Run the following command as privileged user::

  $ wget http://yum.centreon.com/standard/2.2/ces-standard.repo -O /etc/yum.repos.d/ces-standard.repo

The repo file is now installed.

************
Installation
************

Simply execute the following commands::

  $ yum clean all
  $ yum install centreon-clapi


****************
Web Installation
****************

In order to finish the installation, connect to your Centreon web interface, then go to Administration > Modules and click on the Installation button:

.. image:: /_static/images/installation/web_install_1.png
   :align: center

.. image:: /_static/images/installation/web_install_2.png
   :align: center


The module is now installed.

.. image:: /_static/images/installation/web_install_3.png
   :align: center
