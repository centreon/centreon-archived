================
Install a poller
================

Using Centreon ISO
------------------

The installation process is identical to a Centreon central server installed from the ISO file of Centreon.

.. note::
    Refer to the documentation: :ref:`installation<installisoel7>`

For the question **Which server type would you like to install?** choose the option **Poller server**.

.. image:: /images/user/configuration/10advanced_configuration/07installpoller.png
    :align: center

Go to :ref:`SSH Key Exchange chapter to continu<sskkeypoller>`.

Using Centreon packages
-----------------------

SELinux should be disabled. In order to do this, you have to edit the file
*/etc/selinux/config* and replace "enforcing" by "disabled"::

    SELINUX=disabled

.. note::
    After saving the file, please reboot your operating system to apply the
    changes.

A quick check of SELinux status::

    $ getenforce
    Disabled

To install Centreon software from the repository, you should first install
centreon-release package which will provide the repository file.

Centreon repository installation::

    # wget http://yum.centreon.com/standard/18.10/el7/stable/noarch/RPMS/centreon-release-18.10-1.el7.centos.noarch.rpm -O /tmp/centreon-release-18.10-1.el7.centos.noarch.rpm
    # yum install --nogpgcheck /tmp/centreon-release-18.10-1.el7.centos.noarch.rpm

The repository is now installed.

Perform the command::

    # yum install centreon-poller-centreon-engine

Go to :ref:`SSH Key Exchange chapter to continu<sskkeypoller>`.
