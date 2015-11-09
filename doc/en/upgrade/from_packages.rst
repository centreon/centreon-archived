.. _upgrade_from_packages:

=================
Upgrade using RPM
=================

In order to update the Centreon monitoring interface, simply run the following command:

 ::

 yum update centreon

Then, if all is ok, go on the Centreon interface and log out and follow the steps :

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

