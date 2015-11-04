==========
Nagios CFG
==========

Overview
--------

Object name: **NAGIOSCFG**

Show
----

In order to list available Nagios conf, use the **SHOW** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a show 
  nagios id;nagios name;instance;nagios comment
  1;Nagios CFG 1;Central;Default Nagios.cfg
  [...]

Columns are the following :

======= ===========================================
Order	Description
======= ===========================================
1	Nagios ID

2	Nagios configuration name

3	Instance that is linked to nagios.cfg

4	Comments regarding the configuration file
======= ===========================================


Add
---

In order to add a Nagios conf, use the **ADD** action::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a add -v "Nagios cfg for poller test;Poller test;Just a small comment" 

Required fields are:

======== ===========================================
Order	 Description
======== ===========================================
1	 Nagios configuration name

2	 Instance that is linked to nagios.cfg

3	 Comment regarding the configuration file
======== ===========================================


Del
---

If you want to remove a Nagios conf, use the **DEL** action. The name is used for identifying the configuration to delete::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a del -v "Nagios cfg for poller test" 


Setparam
--------

If you want to change a specific parameter of a Nagios conf, use the **SETPARAM** action. The name is used for identifying the configuration to update::

  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a setparam -v "Nagios cfg for poller test;cfg_dir;/usr/local/nagios/etc" 

Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Nagios configuration

2	Parameter name

3	Parameter value
======= =================================

Parameters that you may change are:

================ =============================================================================================================================
Column	         Description
================ =============================================================================================================================
nagios_name	 Name

instance	 Instance that is linked to nagios.cfg

broker_module	 example: [...] -v "Nagios CFG 1;broker_module;/usr/local/nagios/bin/ndomod.o config_file=/usr/local/nagios/etc/ndomod.cfg", 
                 you can use a | delimiter for defining multiple broker modules

nagios_activate	 *1* if activated, *0* otherwise

*	         Centreon CLAPI handles pretty much all the options available in a nagios configuration file. 
                 Because the list is quite long, it is best to refer to the official documentation of Nagios
================ =============================================================================================================================


Addbrokermodule
---------------

If you want to add new broker module without removing existing modules, use the **ADDBROKERMODULE**::
  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a addbrokermodule -v "Nagios cfg for poller test;/usr/lib64/centreon-engine/externalcmd.so"


Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Nagios configuration

2	Module name
======= =================================

To add multiple modules in one line, it will put the separator "|" between the name of the modules
  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a addbrokermodule -v "Nagios cfg for poller test;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.xml"


Delbrokermodule
---------------

If you want to delete broker module, use the **DELBROKERMODULE**::
  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a delbrokermodule -v "Nagios cfg for poller test;/usr/lib64/centreon-engine/externalcmd.so"


Arguments are composed of the following columns:

======= =================================
Order	Column description
======= =================================
1	Name of Nagios configuration

2	Module name
======= =================================

To delete multiple modules in one line, it will put the separator "|" between the name of the modules
  [root@centreon ~]# ./centreon -u admin -p centreon -o NAGIOSCFG -a delbrokermodule -v "Nagios cfg for poller test;/usr/lib64/centreon-engine/externalcmd.so|/etc/centreon-broker/central-module.xml"
