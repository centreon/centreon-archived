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

This new page enables users to make their own views with various
widgets and they are able to share their personalised views with their
colleagues!

See the :ref:`user guide <widgets_user_guide>` to learn more about
this great feature.

Support for multiple LDAP servers
=================================

New *autologin* mechanism
=========================

A better :ref:`autologin <autologin>` mechanism has been introduced in
this version. Now using randomly generated keys, it allows you to
access specific pages without beeing prompted for a username and a
password.

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

A :ref:`new autologin mechanism <autologin>` has been added in
Centreon 2.4. More secured than the previous one, it will soon replace
it. If you currently use this feature, we recommend upgrading to the
new one as soon as you can.


Centstorage
===========

Supported data source types
---------------------------

*Centreon Broker* now supports all of the RRDtool data source types
(COUNTER, GAUGE, DERIVE and ABSOLUTE). This support will not be added
to *Centstorage* as it will soon be replaced by *Centreon Broker*.
