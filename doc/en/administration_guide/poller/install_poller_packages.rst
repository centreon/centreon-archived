=======================
Using Centreon packages
=======================

************
Installation
************

SELinux should be disabled. In order to do this, you have to edit the file
*/etc/selinux/config* and replace "enforcing" by "disabled"::

    SELINUX=disabled

.. note::
    After saving the file, please reboot your operating system to apply the
    changes.

A quick check of SELinux status::

    $ getenforce
    Disabled

Add firewall rules or disable the firewall by running following commands: ::

    # systemctl stop firewalld
    # systemctl disable firewalld
    # systemctl status firewalld

To install Centreon software from the repository, you should first install
centreon-release package which will provide the repository file.

Centreon repository installation::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-2.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-2.el7.centos.noarch.rpm

The repository is now installed.

Perform the command::

    # yum install centreon-poller-centreon-engine

.. include:: ssh_key.rst

.. include:: wizard_add_poller.rst
