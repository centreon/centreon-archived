.. _install:

############
Installation
############

This chapter describes how to install your Centreon monitoring platform.

The monitoring platform may be installed in several ways. However, 
**we strongly recommend using Centreon ISO to install your platform**.
Enjoy of our work of industrialization during install and update steps of your
the environment. Also enjoy optimizations installed by default on the system.

Centreon Installation can be performed from source (tar.gz) but the work is more
complex. In addition the installer shall be supported by the community.

Before installation, be sure to follow the prerequisites installation and sizing
(resources CPU, memory, disks, partitioning, etc ...). Also take care to choose
the type of architecture that should be set up for your needs.

Finally, you can install the platform.

.. toctree::
    :maxdepth: 1

    prerequisites
    architecture/index
    downloads
    from_iso
    from_packages
    from_VM
    from_sources

To quickly test Centreon from a CentOS or Red Hat 7.x, you can run the following command as **root**: ::

    # curl -L https://raw.githubusercontent.com/centreon/centreon/master/unattended.sh | sh
