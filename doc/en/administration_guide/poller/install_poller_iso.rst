======================
Using Centreon el7 ISO
======================

************
Installation
************

This process is identical to the one used for installing the Central Server
from a Centreon ISO file.

.. note::
    Refer to the documentation: :ref:`installation<installisoel7>`

Instead of choosing **Central with database**, you will select **Poller** and
click **Done**. Please see the sizing guidelines for the poller server located
`here <https://documentation.centreon.com/docs/centreon/en/latest/installation/prerequisites.html>`_. 

.. image:: /images/user/configuration/10advanced_configuration/07installpoller.png
    :align: center

.. include:: ssh_key.rst

.. include:: wizard_add_poller.rst
