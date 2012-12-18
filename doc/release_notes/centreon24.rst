============
Centreon 2.4
============

**********
What's new
**********

Better integration with Centreon Engine and Centreon Broker
===========================================================

Custom views
============

Support for multiple LDAP servers
=================================

.. _autologin:

New *autologin* mechanism
=========================

***************
Important notes
***************

Administration
==============

Communication with pollers
--------------------------

The default system user used by *Centcore* to communicate with pollers
has changed from ``nagios`` to ``centron``.

.. warning::

   Faire référence à la doc. de mise à jour pour traiter ce cas

Web interface
=============

Autologin
---------

A :ref:`autologin` has been added in Centreon 2.4. More secured than
the previous one, it will soon replace it. If you currently use this
feature, we recommend upgrading to the new one as soon as you can.


Centstorage
===========

Supported data source types
---------------------------

*Centreon Broker* now supports all of the RRDtool data source types
(COUNTER, GAUGE, DERIVE and ABSOLUTE). This support will not be added
to *Centstorage* as it will soon be replaced by *Centreon Broker*.
