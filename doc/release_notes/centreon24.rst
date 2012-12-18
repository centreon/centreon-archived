============
Centreon 2.4
============

**********
What's new
**********

Better integration with Centreon Engine and Centreon Broker
===========================================================

The :ref:`installation <centreon_install>` process has been reviewed: 
it is now possible to specify the monitoring engine (Centreon Engine or Nagios) 
and the event broker module (Centreon Broker or NDOUtils). All you
need to do right after a fresh installation is export your configuration files, then reload your
monitoring engine and the monitoring system should be up and running!

This version offers the possibility to define the :ref:`connectors <centreon-engine:obj_def_connector>` for Centreon Engine. Obviously,
you do not need to configure these connectors if you are still using Nagios.

It's been said that Centreon Broker can be cumbersome to configure, especially if you are not
familiar with its functioning. Centreon 2.4 offers a configuration wizard now!


Custom views
============

This new page enables users to make their own views with various
widgets and they are able to share their custom views with their
colleagues!

See the :ref:`user guide <widgets_user_guide>` to learn more about
this feature.


Support for multiple LDAP servers
=================================

The LDAP authentication system is much more robust than before.
Indeed, it is now possible to have :ref:`multiple LDAP configurations <ldap>` on
top of the failover system. The LDAP import form will let you choose the
LDAP server to import from.

Make sure that all your LDAP parameters are correctly imported after an upgrade.


New *autologin* mechanism
=========================

A better :ref:`autologin <autologin>` mechanism has been introduced in
this version. Now using randomly generated keys, it allows you to
access specific pages without beeing prompted for a username and a
password.

Database indexes verification tool
==================================

If you upgrade from an old version of Centreon, now you can :ref:`check the
existence of all database indexes <synchronizing-indexes>` to ensure maximum performance

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

See the :ref:`Centreon Broker documentation <centreon-broker:graphic_types>` to learn how you can
convert your existing plugins.
