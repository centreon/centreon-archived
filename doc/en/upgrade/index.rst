.. _upgrade:

#########
Upgrading
#########

This chapter describes how to upgrade your Centreon monitoring platform.

This procedure is linked to your initial version of Centreon. You will have to
**use packages** if you already installed using Centreon ISO or an RPM, and
source files if you installed from sources. Before upgrading Centreon, remember
to make a backup your system.

.. warning::
    If you try to migrate a platform using **Centreon Poller Display 1.6.x**,
    refer to :ref:`migration procedure <migratefrompollerdisplay>`.

.. warning::
   The upgrade process can start only from versions **2.8.0** and later. If you have
   an earlier version, please update to the latest *2.8.x* version first.

.. toctree::
    :maxdepth: 2

    from_packages_1904
    from_packages_1810
    from_packages_34
    from_sources
