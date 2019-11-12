==============
Centreon 2.4.5
==============

Important notes
---------------

Connector
#########

You can now linked a command to a connector from the connector form in `Configuration` > `Commands` > `Connectors`.


Centreon Broker
###############

Centreon 2.4.x branch is now compatible with Centreon Broker 2.5.x branch.
Also several options have been added in Centreon Broker configuration form accessible in `Configuration` > `Centreon` > `Configuration` (Below Centreon-Broker label in the left panel).
Here the new options:

    * "Write timestamp" in `General` tab: To enable or disable timestamp logging in each log line (disable this option is useful with when Centreon-Broker is used with Nagios)
    * "Write thread id" in `General` tab: To enable or disable thread id logging in each log line
    * "Write metrics" in `Output` tab with `RRD - RRD file generator`: To enable or disable the update of the performance graph
    * "Write status" in `Output` tab with `RRD - RRD file generator`: To enable or disable the update of the status graph
    * "Store performance data in data_bin" in `Output` tab with `Storage - Perfdata Generator (Centreon Storage)`: To enable or disable insertion of performance data in data_bin table
    * "Insert in index data" in `Output` tab with `Storage - Perfdata Generator (Centreon Storage)`: Allow Centreon-Broker to create entries in index_data table (use with caution)

==============
Centreon 2.4.4
==============

Important notes
---------------

Graphs
######

It is now possible to set RRD graphs' to "DERIVE" and "ABSOLUTE" type. In order 
to do so go to `Administration` > `Options` > `CentStorage` > `Manage`, then
click on the metric you would like to update. In the "More actions" toolbar, you 
will now see the new data source types.


Monitoring consoles
###################

A new option is available, allowing you to choose the display order of the 
monitored resources. The new option is available in `Administration` > `Options`, 
in the `Problem display properties` section.

==============
Centreon 2.4.1
==============

Important notes
---------------

Connectors
##########

If you are already using the *Centreon Connectors*, please note that the connector
path is no longer called with user variable *$USER3$*. It is instead in the 
``Configuration`` > ``Centreon`` > ``Pollers`` > ``Centreon Connector path``. In that regard,
be sure to fill this field and update the connector command line in ``Configuration`` > 
``Commands`` > ``Connectors`` by removing the *$USER3$* prefix.

i.e::

    $USER3$/centreon_connector_perl

should become::

    centreon_connector_perl

Once you're done with updating those configurations, you may delete the former *$USER3$*
as it will be no longer used.


============
Centreon 2.4
============

What's new?
-----------

Better integration with Centreon Engine and Centreon Broker
###########################################################

The :ref:`installation <centreon_install>` process has been reviewed: 
it is now possible to specify the monitoring engine (Centreon Engine or Nagios) 
and the event broker module (Centreon Broker or NDOUtils). All you
need to do right after a fresh installation is export your configuration files, then reload your
monitoring engine and the monitoring system should be up and running!

This version offers the possibility to define the :ref:`connectors <centreon-engine:obj_def_connector>` 
for Centreon Engine. Obviously, you do not need to configure these connectors if you are still using Nagios.

It's been said that Centreon Broker can be cumbersome to configure, especially if you are not
familiar with its functioning. Centreon 2.4 offers a configuration wizard now!


Custom views
############

This new page enables users to make their own views with various
widgets and they are able to share their custom views with their
colleagues!

See the :ref:`user guide <widgets_user_guide>` to learn more about
this feature.


Support for multiple LDAP servers
#################################

The LDAP authentication system is much more robust than before.
Indeed, it is now possible to have :ref:`multiple LDAP configurations <ldapconfiguration>` on
top of the failover system. The LDAP import form will let you choose the
LDAP server to import from.

Make sure that all your LDAP parameters are correctly imported after an upgrade.


New *autologin* mechanism
#########################

A better :ref:`autologin <autologin>` mechanism has been introduced in
this version. Now using randomly generated keys, it allows you to
access specific pages without being prompted for a username and a
password.

Database indexes verification tool
##################################

If you upgrade from an old version of Centreon, now you can :ref:`check the
existence of all database indexes <synchronizing-indexes>` to ensure maximum performance

Important notes
---------------

Administration
##############

Communication with pollers
##########################

The default system user used by *Centcore* to communicate with pollers
has changed from ``nagios`` to ``centreon``.

Plugins
#######

For better performances, we advise you to use ``check_icmp`` 
instead of ``check_ping`` if you are in an IPv4 network, that is
(check_icmp is not yet compatible with IPv6). Switching from ``check_ping`` to
``check_icmp`` should be quite simple as the plugins take the same parameters.
All you have to do is change the check commands: ``check_centreon_ping``, 
``check_host_alive`` and all the commands that call ``check_ping``.

Web interface
-------------

Autologin
#########

A :ref:`new autologin mechanism <autologin>` has been added in
Centreon 2.4. More secured than the previous one, it will soon replace
it. If you currently use this feature, we recommend upgrading to the
new one as soon as you can.

Centreon Broker init script
###########################

If you are using *Centreon Broker*, make sure to fill the *Start script for broker daemon* 
parameter in ``Administration`` > ``Options`` > ``Monitoring``. RRD graphs cannot be rebuilt
if this parameter is omitted!

Centcore options
################

Two parameters have been added into the ``Administration`` > ``Options`` > ``Monitoring`` page:

* Enable Perfdata Synchronization (Centcore)
* Enable Logs Synchronization (Centcore)

For performance issues, these options must be disabled if your monitoring system is running
with Centreon Broker.

Resource.cfg and CGI.cfg
########################

The resource and CGI configuration objects are now specific to each monitoring poller. The
values of $USERx$ macros can be different from one poller to another.

Interval length
###############

The ``interval_length`` is now a global parameter that you have to set in ``Administration`` > ``Options`` 
> ``Monitoring``, although it should be left at ``60 seconds`` in most cases.

Centstorage
-----------

Supported data source types
###########################

*Centreon Broker* now supports all of the RRDtool data source types
(COUNTER, GAUGE, DERIVE and ABSOLUTE). This support will not be added
to *Centstorage* as it will soon be replaced by *Centreon Broker*.

See the :ref:`Centreon Broker documentation <centreon-broker:graphic_types>` to learn how you can
convert your existing plugins.
