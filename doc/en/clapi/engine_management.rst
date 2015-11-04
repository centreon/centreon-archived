=================
Poller management
=================

List available pollers
----------------------

In order to list available pollers, use the **POLLERLIST** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERLIST
  1       Local Poller
  2       Remote Poller

Where 1 is the id of "Local Poller" and 2 is the id of "Remote Poller".


Generate local configuration files for a poller
-----------------------------------------------

In order to generate configuration files for poller "Local Poller" of id 1, use the **POLLERGENERATE** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERGENERATE -v 1
  Configuration files generated for poller 1

You can generate the configuration using the poller name::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERGENERATE -v "Local Poller"
  Configuration files generated for poller 'Local Poller'


Test monitoring engine configuration of a poller
------------------------------------------------

In order to test configuration files for poller "Remote Poller" of id 2, use the **POLLERTEST** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERTEST -v 1
  OK: Nagios Poller 2 can restart without problem...

You can test the configuration using the poller name::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERTEST -v "Local Poller"
  Warning: Nagios Poller poller can restart but configuration is not optimal. Please see debug bellow :
  ---------------------------------------------------------------------------------------------------
  [1440681047] [15559] Reading main configuration file '/usr/share/centreon//filesGeneration/nagiosCFG/5/nagiosCFG.DEBUG'.
  [1440681047] [15559] Warning: temp_path variable ignored
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hosts.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostTemplates.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/serviceTemplates.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/services.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/misccommands.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/checkcommands.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactgroups.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactTemplates.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contacts.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostgroups.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/servicegroups.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/timeperiods.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/escalations.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/dependencies.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/connectors.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-command.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contact.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contactgroup.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-dependencies.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-escalations.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-host.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-services.cfg'
  [1440681047] [15559] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-timeperiod.cfg'
  [1440681047] [15559] Reading resource file '/usr/share/centreon/filesGeneration/nagiosCFG/5/resource.cfg'
  [1440681047] [15559] Checking global event handlers...
  [1440681047] [15559] Checking obsessive compulsive processor commands...
  [1440681047] [15559]
  [1440681047] [15559] Checked 55 commands.
  [1440681047] [15559] Checked 0 connectors.
  [1440681047] [15559] Checked 7 contacts.
  [1440681047] [15559] Checked 0 host dependencies.
  [1440681047] [15559] Checked 0 host escalations.
  [1440681047] [15559] Checked 0 host groups.
  [1440681047] [15559] Checked 1 hosts.
  [1440681047] [15559] Checked 0 service dependencies.
  [1440681047] [15559] Checked 0 service escalations.
  [1440681047] [15559] Checked 0 service groups.
  [1440681047] [15559] Checked 1 services.
  [1440681047] [15559] Checked 5 time periods.
  [1440681047] [15559]
  [1440681047] [15559] Total Warnings: 1
  [1440681047] [15559] Total Errors:   0

  ---------------------------------------------------------------------------------------------------
  Return code end : 0


Move monitoring engine configuration files
------------------------------------------

In order to move configuration files for poller "Local Poller" of id 1 to the final engine directory, use the **CFGMOVE** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a CFGMOVE -v 2
  OK: All configuration will be send to 'Remote Poller' by centcore in several minutes.
  Return code end : 1

You can move the configuration files using the poller name::

  [root@centreon core]# ./centreon -u admin -p centreon -a CFGMOVE -v "Remote Poller"
  OK: All configuration will be send to 'Remote Poller' by centcore in several minutes.
  Return code end : 1


Restart monitoring engine of a poller
-------------------------------------

In order to restart the monitoring process on poller "Local Poller" of id 1, use the the **POLLERRESTART** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERRESTART -v 2
  OK: A restart signal has been sent to 'Remote Poller'
  Return code end : 1

You can restart the poller using its name::

  [root@centreon core]# ./centreon -u Remote Poller -p centreon -a POLLERRESTART -v "Remote Poller"
  OK: A restart signal has been sent to 'Remote Poller'
  Return code end : 1


All in one command
------------------

Use the **APPLYCFG** command in order to execute all of the above with one single command::

  [root@centreon core]# ./centreon -u admin -p centreon -a APPLYCFG -v 1

You can execute using the poller name::

  [root@centreon core]# ./centreon -u admin -p centreon -a APPLYCFG -v "Remote Poller"
 

This will execute **POLLERGENERATE**, **POLLERTEST**, **CFGMOVE** and **POLLERRESTART**.


Reload monitoring engine of a poller
------------------------------------

In order to reload the monitoring process on poller "Remote Poller" of id 2, use the **POLLERRELOAD** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERRELOAD -v 2
  OK: A reload signal has been sent to Remote Pollerpoller'
  Return code end : 1

You can reload poller using its name::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLERRELOAD -v "Remote Poller"
  OK: A reload signal has been sent to 'Remote Poller'
  Return code end : 1


Execute post generation commands of a poller
--------------------------------------------

In order to execute post generation commands of a poller, use the **POLLEREXECCMD** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLEREXECCMD -v 2
  Running configuration check...done.
  Reloading nagios configuration...done

You can execute post generation commands of a poller using its name::

  [root@centreon core]# ./centreon -u admin -p centreon -a POLLEREXECCMD -v "Remote Poller"
  Running configuration check...done.
  Reloading nagios configuration...done


Perform an all-in-one command
-----------------------------

It is possible to perform the following commands all at the same time: POLLERGENERATE, POLLERTEST, CFGMOVE, POLLERRESTART and POLLEREXECCMD.

In order to do that, use the **APPLYCFG** command::

  [root@centreon core]# ./centreon -u admin -p centreon -a APPLYCFG -v 5
  2015-08-27 16:52:32 - APPLYCFG
  Configuration files generated for poller '5'

  Warning: Nagios Poller 5 can restart but configuration is not optimal. Please see debug bellow :
  ---------------------------------------------------------------------------------------------------
  [1440687152] [18827] Reading main configuration file '/usr/share/centreon//filesGeneration/nagiosCFG/5/nagiosCFG.DEBUG'.
  [1440687152] [18827] Warning: temp_path variable ignored
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hosts.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostTemplates.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/serviceTemplates.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/services.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/misccommands.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/checkcommands.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactgroups.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactTemplates.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contacts.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostgroups.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/servicegroups.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/timeperiods.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/escalations.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/dependencies.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/connectors.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-command.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contact.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contactgroup.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-dependencies.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-escalations.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-host.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-services.cfg'
  [1440687152] [18827] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-timeperiod.cfg'
  [1440687152] [18827] Reading resource file '/usr/share/centreon/filesGeneration/nagiosCFG/5/resource.cfg'
  [1440687152] [18827] Checking global event handlers... 
  [1440687152] [18827] Checking obsessive compulsive processor commands...
  [1440687152] [18827]
  [1440687152] [18827] Checked 55 commands.
  [1440687152] [18827] Checked 0 connectors.
  [1440687152] [18827] Checked 7 contacts.
  [1440687152] [18827] Checked 0 host dependencies.
  [1440687152] [18827] Checked 0 host escalations.
  [1440687152] [18827] Checked 0 host groups.
  [1440687152] [18827] Checked 1 hosts.
  [1440687152] [18827] Checked 0 service dependencies.
  [1440687152] [18827] Checked 0 service escalations.
  [1440687152] [18827] Checked 0 service groups.
  [1440687152] [18827] Checked 1 services.
  [1440687152] [18827] Checked 5 time periods.
  [1440687152] [18827]
  [1440687152] [18827] Total Warnings: 1
  [1440687152] [18827] Total Errors:   0

  ---------------------------------------------------------------------------------------------------

  OK: All configuration will be send to 'Remote Poller' by centcore in several minutes.

You can perform poller using its name::

  [root@centreon core]# ./centreon -u admin -p centreon -a APPLYCFG -v "Remote Poller"
  2015-08-27 16:54:46 - APPLYCFG
  Configuration files generated for poller 'Remote Poller'

  Warning: Nagios Poller poller can restart but configuration is not optimal. Please see debug bellow :
  ---------------------------------------------------------------------------------------------------
  [1440687287] [18877] Reading main configuration file '/usr/share/centreon//filesGeneration/nagiosCFG/5/nagiosCFG.DEBUG'.
  [1440687287] [18877] Warning: temp_path variable ignored
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hosts.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostTemplates.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/serviceTemplates.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/services.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/misccommands.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/checkcommands.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactgroups.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contactTemplates.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/contacts.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/hostgroups.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/servicegroups.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/timeperiods.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/escalations.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/dependencies.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/connectors.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-command.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contact.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-contactgroup.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-dependencies.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-escalations.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-host.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-services.cfg'
  [1440687287] [18877] Processing object config file '/usr/share/centreon/filesGeneration/nagiosCFG/5/centreon-bam-timeperiod.cfg'
  [1440687287] [18877] Reading resource file '/usr/share/centreon/filesGeneration/nagiosCFG/5/resource.cfg'
  [1440687287] [18877] Checking global event handlers...
  [1440687287] [18877] Checking obsessive compulsive processor commands...
  [1440687287] [18877]
  [1440687287] [18877] Checked 55 commands.
  [1440687287] [18877] Checked 0 connectors.
  [1440687287] [18877] Checked 7 contacts.
  [1440687287] [18877] Checked 0 host dependencies.
  [1440687287] [18877] Checked 0 host escalations.
  [1440687287] [18877] Checked 0 host groups.
  [1440687287] [18877] Checked 1 hosts.
  [1440687287] [18877] Checked 0 service dependencies.
  [1440687287] [18877] Checked 0 service escalations.
  [1440687287] [18877] Checked 0 service groups.
  [1440687287] [18877] Checked 1 services.
  [1440687287] [18877] Checked 5 time periods.
  [1440687287] [18877]
  [1440687287] [18877] Total Warnings: 1
  [1440687287] [18877] Total Errors:   0

  ---------------------------------------------------------------------------------------------------

  OK: All configuration will be send to 'Remote Poller' by centcore in several minutes.

  Return code end : 1
